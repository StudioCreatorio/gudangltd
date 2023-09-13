
function accordion_script() {

	const accordionInstances = new WeakMap();

	class Accordion {
		constructor(element, options) {
			this.element = element;
			this.options = options;
			this.removePlaceholder();

			if (!this.validateStructure()) {
				return;
			}
			this.removePlaceholderChild();
			this.accordionItems = this.element.querySelectorAll(':scope > .fr-accordion__item')
			this.lastOpenedItem = null;
			this.init();
			accordionInstances.set(this.element, this);
		}

		init() {
			this.setEventListeners();
			this.handleFirstItemOpened();
			this.handleAllItemsExpanded();
			this.handleExpandedClass();
			this.handleScrollToHash();
			this.handleShowDuration();
			this.addTabindex();
			this.addIdToBody();
			this.addAriaControls();
			this.addIdToHeader();
			this.addAriaLabelledBy();
			this.addFaqSchema();

			this.ITEMS(item => {
				if (this.HEADER(item).getAttribute('aria-expanded') !== 'true') {
					this.updateFocusableTabIndex(item, false);
				}
			});

			this.ITEMS(item => {
				if (this.hasCurrentPageLink(item) ) {
					this.expandItem(item);
				}
			});

		}

		BODY(item) {
			return item.querySelector('.fr-accordion__body') || item.children[1];
		}

		HEADER(item) {
			return item.querySelector('.fr-accordion__header') || item.children[0];
		}

		ITEMS(init) {
			this.accordionItems.forEach(item => {
				if (!item.classList.contains('brx-nestable-children-placeholder')) {
					init(item);
				}
			})
		}


		removePlaceholder() {
			const placeholder = this.element.querySelector('.brx-nestable-children-placeholder');
			const accordionChildren = this.element.querySelectorAll('.fr-accordion__item');

			if (placeholder) {
				placeholder.remove();
			}

			// If there are no children of accordion, add the placeholder back
			if (accordionChildren.length === 0) {
				const newPlaceholder = document.createElement('div');
				newPlaceholder.className = 'brx-nestable-children-placeholder fr-accordion__item';
				this.element.appendChild(newPlaceholder);
			}
		}


		setEventListeners() {

			this.ITEMS((item) => this.attachHeaderListeners(item));
		}

		attachHeaderListeners(item) {
			const header = this.HEADER(item);
			header.addEventListener('click', () => {
				this.headerInteraction(item);
			});
			header.addEventListener('keydown', (e) => {
				if (e.key === 'Enter') {
					this.headerInteraction(item);
				}
			});
			header.addEventListener('keyup', (e) => {
				if (e.key === ' ') {
					this.headerInteraction(item);
				}
			});
		}

		headerInteraction(item) {
			if (this.options.closePreviousItem && this.options.allItemsExpanded) {
				this.ITEMS((otherItem) => {
					if (otherItem !== item) {
						this.collapseItem(otherItem);
					}
				});
			} else if (this.options.closePreviousItem && this.lastOpenedItem && this.lastOpenedItem !== item) {
				this.collapseItem(this.lastOpenedItem);
			}
			this.toggleItem(item);
			this.lastOpenedItem = this.HEADER(item).getAttribute('aria-expanded') === 'true' ? item : null;
		}

		toggleItem(item) {
			if (this.HEADER(item).getAttribute('aria-expanded') === 'true') {
				this.collapseItem(item);
			} else {
				this.expandItem(item);
			}
		}

		collapseItem(item) {
			const body = this.BODY(item);
			body.style.height = body.offsetHeight + 'px';
			setTimeout(() => {
				body.style.height = 0;
			}, 0);
			this.HEADER(item).setAttribute('aria-expanded', 'false');
			// Dispatch the 'accordionItemCollapsed' event
			const collapseEvent = new CustomEvent('accordionItemCollapsed', { detail: { item: item } });
			this.element.dispatchEvent(collapseEvent);
			this.flipIcon(item);
			this.updateFocusableTabIndex(item, false);
		}

		expandItem(item) {
			const body = this.BODY(item);
			body.style.height = body.scrollHeight + 'px';
			this.HEADER(item).setAttribute('aria-expanded', 'true');
			// Dispatch the 'accordionItemExpanded' event
			const expandEvent = new CustomEvent('accordionItemExpanded', { detail: { item: item } });
			this.element.dispatchEvent(expandEvent);
			this.flipIcon(item);
			this.updateFocusableTabIndex(item, true);
		}

		// fr-accordion__icon flip icon on expand and collapse
		flipIcon(item) {
			const icon = this.HEADER(item).querySelector('.fr-accordion__icon');
			if (icon) {
				// add transition property with time of showDuration
				icon.style.transition = `transform ${this.options.showDuration}ms ease-in-out`;
				icon.classList.toggle('fr-accordion__icon--flipped');
			}
		}


		handleFirstItemOpened() {
			if (!this.options.firstItemOpened) return
			this.expandItem(this.accordionItems[0]);
			this.lastOpenedItem = this.accordionItems[0];
		}

		handleAllItemsExpanded() {
			if (!this.options.allItemsExpanded) return
			this.ITEMS((item) => this.expandItem(item));
		}

		handleExpandedClass() {
			if (!this.options.expandedClass) return
			this.ITEMS((item) => {
				if (item.classList.contains('fr-accordion__item--expanded')) {
					this.expandItem(item);
				}
			});
		}

		handleScrollToHash() {
			if (!this.options.scrollToHash) return
			const hash = window.location.hash;
			if (hash) {
				const item = this.element.querySelector(hash);
				console.log(item);
				if (item) {
					this.scrollToItemAndExpand(item);
				}
			} else {
				console.log('no hash');
				this.ITEMS((item) => {
					const id = item.id;
					if (id) {
						const links = document.querySelectorAll(`a[href="#${id}"]`);
						links.forEach((link) => {
							link.addEventListener('click', (e) => {
								this.scrollToItemAndExpand(item);
							});
						});
					}
				});
			}
		}

		scrollToItemAndExpand(item) {
			this.expandItem(item);
			const header = this.HEADER(item);
			const headerOffset = header.offsetTop;
			const headerHeight = header.offsetHeight;
			const offsetPosition = headerOffset - headerHeight - this.options.scrollToHashOffset;
			window.scrollTo({
				top: offsetPosition,
				behavior: 'smooth'
			});
		}





		handleShowDuration() {
			console.log(this.options.showDuration);
			if (!this.options.showDuration) return
			this.ITEMS((item) => {
				this.BODY(item).style.transitionDuration = this.options.showDuration + 'ms';
				// for header transition
				this.HEADER(item).style.transitionDuration = this.options.showDuration + 'ms';
				// for __title in header
				this.HEADER(item).querySelector('.fr-accordion__title').style.transitionDuration = this.options.showDuration + 'ms';
			});
		}

		addTabindex() {
			this.ITEMS((item) => {
				this.HEADER(item).setAttribute('tabindex', 0);
			});
		}

		addIdToBody() {
			this.ITEMS((item) => {
				if (!this.BODY(item).id) {
					this.BODY(item).id = 'fr-accordion__body-' + Math.random().toString(36).substr(2, 9);
				}
			});
		}

		addIdToHeader() {
			this.ITEMS((item) => {
				if (!this.HEADER(item).id) {
					this.HEADER(item).id = 'fr-accordion__header-' + Math.random().toString(36).substr(2, 9);
				}
			});
		}

		addAriaControls() {
			this.ITEMS((item) => {
				this.HEADER(item).setAttribute('aria-controls', this.BODY(item).id);
			});
		}


		addAriaLabelledBy() {
			this.ITEMS((item) => {
				this.BODY(item).setAttribute('aria-labelledby', this.HEADER(item).id);
			});
		}

		addRoleRegion() {
			this.ITEMS((item) => {
				this.BODY(item).setAttribute('role', 'region');
			});
		}

		addFaqSchema() {
			if (this.options.faqSchema) {

				const faqData = [];

				this.ITEMS((item) => {
					const question = this.HEADER(item).textContent.trim();
					const answer = this.BODY(item).textContent.trim();

					faqData.push({
						"@type": "Question",
						"name": question,
						"acceptedAnswer": {
							"@type": "Answer",
							"text": answer,
						},
					});
				});

				const faqSchema = {
					"@context": "https://schema.org",
					"@type": "FAQPage",
					"mainEntity": faqData,
				};

				const script = document.createElement('script');
				script.type = 'application/ld+json';
				script.innerHTML = JSON.stringify(faqSchema);
				document.head.appendChild(script);
			}
		}


		validateStructure() {
			if (this.element.children.length === 0) {
				console.warn('The fr-accordion has no children.');
				this.addWarning();
				return false;
			}

			for (const child of this.element.children) {
				if (!child.classList.contains('fr-accordion__item')) {
					child.classList.add('fr-accordion__item');
				}

				if (child.children.length > 2) {
					console.warn('Remove extra children elements from accordion item:', child);
					this.addWarning();
					return false;
				}

				if (child.children.length < 2 && child.children.length > 0) {
					console.warn('Add a header and a body to accordion item:', child);
					return false;
				}

				// if element tag is ul or or and direct childrent are not li elements
				// add warning "add li elements to ul or ol"

				if (this.element.tagName === 'UL' || this.element.tagName === 'OL') {
					if (child.tagName !== 'LI') {
						console.warn(`Direct child of the "ul" or "ol" element should have a tag of "li" instead of "${child.tagName}"`, child);
					}
				}

			}

			return true;
		}

		removePlaceholderChild() {
			if (this.element.querySelectorAll('.brx-nestable-children-placeholder')) {
				this.element.querySelectorAll('.brx-nestable-children-placeholder').forEach(el => {
					el.remove();
				});
			}
		}

		updateFocusableTabIndex(item, isOpened) {
			const focusableSelectors = 'a[href], button, textarea, input[type="text"], input[type="radio"], input[type="checkbox"], select';
			const focusables = item.querySelectorAll(focusableSelectors);
			focusables.forEach(el => {
				el.setAttribute('tabindex', isOpened ? '0' : '-1');
			});
		}

		hasCurrentPageLink(item) {
			return this.BODY(item).querySelector('a[aria-current="page"]');
		}


		// end of class Accordion
	}

	function framesGetAccordionInstance(element) {
		return accordionInstances.get(element);
	}

	window.getAccordionInstance = framesGetAccordionInstance;

	const accordions = document.querySelectorAll('.fr-accordion');
	accordions.forEach(accordion => {

		if (!accordion.dataset.frAccordionOptions) {
			console.warn('Options not provided for the following fr-accordion element:', accordion);
			return;
		}

		const options = JSON.parse(accordion.dataset.frAccordionOptions);
		new Accordion(accordion, options);
	});


	function accordion_script_bulder() {

		if (!document.querySelector('.brx-body.iframe.mounted')) return;

		const accordions = document.querySelectorAll('.fr-accordion');
		accordions.forEach(accordion => {
			const items = Array.from(accordion.children);
			items.forEach(item => {
				const header = item.children[0];
				const body = item.children[1];
				// toggle height auto on body on header click
				header.addEventListener('click', () => {
					body.style.height = body.style.height === 'auto' ? '0px' : 'auto';
					// toggle aria-expanded on header
					header.setAttribute('aria-expanded', header.getAttribute('aria-expanded') === 'true' ? 'false' : 'true');
				});
			});
		});
	}

	accordion_script_bulder()

}


function wpgb_accordion_script() {
	window.WP_Grid_Builder && WP_Grid_Builder.on('init', function (wpgb) {

		console.dir(wpgb); // Holds all instances.

		wpgb.facets.on('appended', function (nodes) {

			accordion_script()

		});

	});
}


document.addEventListener("DOMContentLoaded", function (e) {
	bricksIsFrontend && accordion_script()
	bricksIsFrontend && wpgb_accordion_script()

});


// We listen every time a grid/template/content is initialized.
