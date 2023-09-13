function table_of_contents_script() {
	const frTableOfContentWrapper = document.querySelector(".fr-toc");
	const frTocContentSelector =
		frTableOfContentWrapper.dataset.frTocContentSelector;
	const frTocShowHeadingsUpTo = frTableOfContentWrapper.dataset.frTocHeading;
	const frToCScrollOffset = parseInt(
		frTableOfContentWrapper.dataset.frTocScrollOffset,
		10
	);
	const frTableOfContentList =
		frTableOfContentWrapper.querySelector(".fr-toc__list");
	frTableOfContentList.removeChild(frTableOfContentList.firstElementChild);
	const frTableOfContentPostContent =
		document.querySelector(frTocContentSelector);
	const frTableOfContentHeadings =
		frTableOfContentPostContent.querySelectorAll("h2, h3, h4, h5, h6");

	createFramesTableOfContentList(frTableOfContentHeadings);

	const frTableOfContentLinks =
		document.querySelectorAll(".fr-toc__list-link");

	if (frToCScrollOffset) {
		toggleActiveClassForFramesTOCLink(
			frToCScrollOffset,
			frTableOfContentLinks
		);
		smoothScrollForFramesTOC(frToCScrollOffset, frTableOfContentLinks);
	}

	const frTableOfContentIsAccordion =
		frTableOfContentWrapper.dataset.frTocAccordion;

	if (frTableOfContentIsAccordion !== "false") {
		accordionForFramesTOC();
	}

	function smoothScrollForFramesTOC(offsetPixels, links) {
		links.forEach((link) => {
			link.addEventListener("click", (e) => {
				e.preventDefault();
				const href = link.getAttribute("href");
				const offsetTop = document.querySelector(href).offsetTop;
				scroll({
					top: offsetTop - 100,
					behavior: "smooth",
				});
			});
		});
	}

	function toggleActiveClassForFramesTOCLink(offsetPixels, links) {
		links.forEach((link) => {
			const href = link.getAttribute("href");
			const targetHeading = document.querySelector(href);
			if (
				targetHeading.getBoundingClientRect().top < offsetPixels + 1 &&
				targetHeading.getBoundingClientRect().top >
					-targetHeading.getBoundingClientRect().height + offsetPixels
			) {
				link.classList.add("fr-toc__list-link--active");
			}
			window.addEventListener("scroll", () => {
				if (
					targetHeading.getBoundingClientRect().top <
					offsetPixels + 1
				) {
					links.forEach((link) =>
						link.classList.remove("fr-toc__list-link--active")
					);
					link.classList.add("fr-toc__list-link--active");
				}
			});
		});
	}

	function accordionForFramesTOC() {
		const tocHeader = document.querySelector(".fr-toc__header");
		const accordionContents = document.querySelectorAll(".fr-toc__body");
		const copyOpenClass = "fr-toc__body--open";
		let target = tocHeader.nextElementSibling;

		if (tocHeader.getAttribute("aria-expanded") === "true") {
			target.style.maxHeight = target.scrollHeight + "px";
		} else {
			target.style.maxHeight = 0;
		}

		tocHeader.onclick = () => {
			let expanded = tocHeader.getAttribute("aria-expanded") === "true";
			if (expanded) {
				closeItem(target, tocHeader);
			} else {
				openItem(target, tocHeader);
			}
		};

		function closeItem(target, btn) {
			btn.setAttribute("aria-expanded", false);
			target.classList.remove(copyOpenClass);
			target.style.maxHeight = 0;
		}
		function openItem(target, btn) {
			btn.setAttribute("aria-expanded", true);
			target.classList.add(copyOpenClass);
			target.style.maxHeight = target.scrollHeight + "px";
		}
	}

	function createFramesTableOfContentList(headings) {
		headings.forEach((heading, index) => {
			const headingId = `fr-toc-content__heading-${index}`;
			heading.id = headingId;
			const headingText = heading.textContent;
			const headingLevel = heading.tagName;
			const listItem = document.createElement("li");
			listItem.classList.add("fr-toc__list-item");
			listItem.innerHTML = `<a href="#${headingId}" class="fr-toc__list-link">${headingText}</a>`;

			if (
				headingLevel === "H2" &&
				(frTocShowHeadingsUpTo === "h6" ||
					frTocShowHeadingsUpTo === "h5" ||
					frTocShowHeadingsUpTo === "h4" ||
					frTocShowHeadingsUpTo === "h3" ||
					frTocShowHeadingsUpTo === "h2")
			) {
				frTableOfContentList.appendChild(listItem);
			}

			if (
				headingLevel === "H3" &&
				(frTocShowHeadingsUpTo === "h6" ||
					frTocShowHeadingsUpTo === "h5" ||
					frTocShowHeadingsUpTo === "h4" ||
					frTocShowHeadingsUpTo === "h3")
			) {
				const lastItem = frTableOfContentList.lastElementChild;
				if (!lastItem.querySelector("ol")) {
					lastItem.innerHTML += '<ol class="fr-toc__list"></ol>';
				}
				lastItem.querySelector("ol").appendChild(listItem);
			}

			if (
				headingLevel === "H4" &&
				(frTocShowHeadingsUpTo === "h6" ||
					frTocShowHeadingsUpTo === "h5" ||
					frTocShowHeadingsUpTo === "h4")
			) {
				const lastItem = frTableOfContentList.lastElementChild;
				const lastSubItem =
					lastItem.querySelector("ol").lastElementChild;
				if (!lastSubItem.querySelector("ol")) {
					lastSubItem.innerHTML += '<ol class="fr-toc__list"></ol>';
				}
				lastSubItem.querySelector("ol").appendChild(listItem);
			}

			if (
				headingLevel === "H5" &&
				(frTocShowHeadingsUpTo === "h6" ||
					frTocShowHeadingsUpTo === "h5")
			) {
				const lastItem = frTableOfContentList.lastElementChild;
				const lastSubItem =
					lastItem.querySelector("ol").lastElementChild;
				const lastSubSubItem =
					lastSubItem.querySelector("ol").lastElementChild;
				if (!lastSubSubItem.querySelector("ol")) {
					lastSubSubItem.innerHTML +=
						'<ol class="fr-toc__list"></ol>';
				}
				lastSubSubItem.querySelector("ol").appendChild(listItem);
			}

			if (headingLevel === "H6" && frTocShowHeadingsUpTo === "h6") {
				const lastItem = frTableOfContentList.lastElementChild;
				const lastSubItem =
					lastItem.querySelector("ol").lastElementChild;
				const lastSubSubItem =
					lastSubItem.querySelector("ol").lastElementChild;
				const lastSubSubSubItem =
					lastSubSubItem.querySelector("ol").lastElementChild;
				if (!lastSubSubSubItem.querySelector("ol")) {
					lastSubSubSubItem.innerHTML +=
						'<ol class="fr-toc__list"></ol>';
				}
				lastSubSubSubItem.querySelector("ol").appendChild(listItem);
			}
		});
	}
}

document.addEventListener("DOMContentLoaded", function (e) {
	bricksIsFrontend && table_of_contents_script();
});
