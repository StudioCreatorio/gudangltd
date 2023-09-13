var geoTypeMap = {
  'country':'countryCode',
  'city':'city',
  'state':'stateProv'
};



window.addEventListener("DOMContentLoaded", () => {
  window.locationOverrideGenerator = new LocationOverrideGenerator(
    'ifso_geo_override',
    ".location-override-generator",
    ".result-shortcode",
    ".shortcode-error",
    true,
    '.preview-container',
    '.drag-table'
  )
  window.ifsoLocGenPipe = {
    accept : function (data){
      window.locationOverrideGenerator.handleLocationFinderResult(data);
    }
  };
})


class LocationOverrideGenerator extends ShortcodeGenerator {
  constructor(prefix, formSelector, shortcodeSelector, errorSelector, instantChange, previewContainerSelector, locationsTableSelector) {
    super(prefix, formSelector, shortcodeSelector, errorSelector, instantChange)

    this.locationsTableSelector = locationsTableSelector
    this.previewContainerSelector = previewContainerSelector
    this.previewContainerElement = document.querySelector(previewContainerSelector)

    this.locationsTable = new DragTable(this.locationsTableSelector)
    this.locationsTable.table.addEventListener('change', () => { this.submitHandler() })

    this.initTypeOptions()
    
    // add to shortcode generator later
    this.joinOperatorNoFilter = function(cat, vals, separator) {
      return ` ${cat}="${vals.filter(v => v !== '').join(separator)}"`
    }

    this.submitHandler = function(event) {
      let formdata = new FormData(this.formElement)

      this.previewContainerElement.innerHTML = '' // clear previous preview
      this.shortcodeElement.value = '' // clear previous shortcode
      this.errorElement.innerHTML = '' // clear previous errors
      this.errorElement.classList.remove('active')

      try {
        let shortcode = this.generateShortcode(formdata)
        this.afterSubmitHandler(shortcode)
      } catch (errors) {
        this.errorElement.classList.add('active')
        let errorLabels = errors.map(e => this.createErrorLabel(e))
        errorLabels.forEach(t => this.errorElement.appendChild(t))
        this.afterSubmitErrorHandler(errors)
      }
    }

    this.afterSubmitHandler = function (shortcode) {
      let currentPromise = this.renderContent(shortcode).then((resultHTML) => {
        if (currentPromise === this.latestRenderContentPromise) {
          this.previewContainerElement.innerHTML = resultHTML
          // disable the live preview onchage to prevent accidenatl page reload
          this.previewContainerElement.querySelectorAll('input,select').forEach(input => input.onchange = undefined)
        }
      }).catch((error) => {
        if (currentPromise === this.latestRenderContentPromise) {
          this.errorElement.classList.add('active')
          this.errorElement.appendChild(this.createErrorLabel('Unable to generate a live preview, please try again later'))
        }
      }).finally(() => {
        if (currentPromise === this.latestRenderContentPromise) {
          this.shortcodeElement.value = shortcode // fill shortcode
        }
      })
      this.latestRenderContentPromise = currentPromise
    }

    this.afterSubmitErrorHandler = (errors) => {
      this.previewContainerElement.innerHTML = ''
    }

    this.operators = [
      [
        ["options"], (cat) => {
          let data = this.locationsTable.data
          if ( data.length === 0 ) throw 'Please add locations to generate a shortcode'

          let values = data.map(location => location.loc_val)
          let labels = data.map(location => location.loc_label)
          let result = this.joinOperatorNoFilter(cat, values, ',')
          let has_extra_data = false
          let extra_data = data.map(el=>{if(typeof(el.extra_fields)!=='undefined'){has_extra_data=true;return {fields:el.extra_fields};}return {};});

          if ( labels.some((label, i) => label !== values[i]) ) result += this.joinOperatorNoFilter('labels', labels, ',')
          if ( has_extra_data ) result+= (' extra-data="' + encodeURIComponent(JSON.stringify(extra_data)) + '"')
          return result
        },
      ],
      [
        ["type"], (cat) => {
          let selectedValue = document.querySelector('.type-option.selected').dataset.value
          return this.omitDefault(cat, [selectedValue], 'select')
        },
      ],
      [
        ["default-option"], (cat, vals) => this.omitDefault(cat, vals, "Select"),
      ],
      [
        ["button"], (cat, vals, formData) => {
          if (vals[0] !== "value") return this.omitDefault(cat, vals, '')
          let valueEntry = formData.find(entry => entry[0] === cat + '-value')
          let copy = valueEntry.map(v => v)
          copy.shift()
          return ` ${cat}="${copy.join(' ')}"`
        }
      ],
      [
        ["geo-type"], (cat) => {
          if( typeof(this.locationsTable.data[0]) !== 'undefined' ){
            let geo_cat = this.locationsTable.data[0].loc_type.toLowerCase();
            let sc_cat = geoTypeMap[geo_cat];

            if(this.locationsTable.containsDissonantGeoTypes()){
              jQuery('.dissonant-geo-types-error').show();
              jQuery('.dissonant-geo-types-error .geo-type-to-use').html(geo_cat);
            }
            else jQuery('.dissonant-geo-types-error').hide()

            return ' ' + cat + '="' + sc_cat + '"'
          }
        },
      ],
      [
        ["redirect"], (cat, vals, formData) => {
          if ( vals[0] !== 'on' ) return '' // returns nothing if the checkbox is disabled or unchecked

          let tableData = this.locationsTable.data
          let tableUrls = tableData.map(location => location.loc_url)
          let url = formData.find(entry => entry[0] === cat + '-value')[1]
          let urlList = tableUrls.map(val => !val ? url : val) // values from the table overwrites the field value
          let listEmpty = urlList.every(val => val === '')
          let listUniform = urlList.every(val => val === urlList[0])
          
          if ( listEmpty ) return ''
          if ( listUniform ) return ` redirect ="${urlList[0]}" ajax="yes"`
          return this.joinOperatorNoFilter('redirects', urlList, ',') + ' ajax="yes"'
        }
      ],
      [
        ["ajax-render", "show-flags", "autodetect-location"], (cat, vals) => {
          if ( vals[0] === 'on' ) return ` ${cat}="yes"`
          return ''
        },
      ],
      [
        ["classname"], (cat, vals) => this.omitDefault(cat, vals, ""),
      ],
      [
        ["button-value", "redirect-value"], () => ''
      ],
    ]
  }

  initTypeOptions() {
    let form = document.querySelector(this.formSelector)
    let options = form.querySelectorAll('.type-option')
    let orientationField = form.querySelector('fieldset[name="orientation"]')
    let defaultOptionField = form.querySelector('fieldset[name="default-option"]')

    options.forEach(opt => {
        opt.addEventListener('click', (event) => {
            options.forEach(o => o.classList.remove('selected'))
            opt.classList.add('selected')

            let isRadio = opt.dataset.value === 'radio'
            defaultOptionField.disabled = isRadio
            orientationField.disabled = !isRadio

            form.dispatchEvent( new Event('change') )
        })
    })
  }

  processLocationData(dataStr) {
    let data = JSON.parse(dataStr);
    let dataWithLabels = data.map(location => {
      if (location.loc_type === 'COUNTRY')
        location.loc_label = all_countries_opts.find(item => item.value === location.loc_val).display_value
      else
        location.loc_label = location.loc_val
      return location
    })
    return dataWithLabels
  }

  handleLocationFinderResult(dataStr) {
    this.locationsTable.table.innerHTML = ''
    let newData = this.processLocationData(dataStr)
    let oldData = this.locationsTable.data
    this.locationsTable.setData(oldData.concat(newData))
  }
}
