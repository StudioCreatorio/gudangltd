function modal_script() {

	const modals = document.querySelectorAll('.fr-modal');
	const focusableElements = 'a[href], button, input, textarea, select, details, [tabindex]:not([tabindex="-1"])';
	const higherIndexTriggerClass = 'fr-modal__trigger--higher-index';
	let closeSelector
	let fadeTime
	let isStartModalVideo
	let isInQueryBuilder = false
	let youtubePlayers = {};
	let youtubeApiInitiated = false;

	// Modal Generator Functions

	function getDataAttributesForModal(modal) {
		if (modal.dataset.frModalTrigger) {
			modalTrigger = modal.dataset.frModalTrigger;

		}
		if (modal.dataset.frModalClose) {
			closeSelector = modal.dataset.frModalClose;

		}
		if (modal.dataset.frModalFadeTime) {
			fadeTime = Number(modal.dataset.frModalFadeTime);

		}
		if (modal.dataset.frVideoAutoplay) {
			isStartModalVideo = modal.dataset.frVideoAutoplay === 'true' ? true : false
		}

		if (modal.dataset.frModalQueryBuilder) {
			isInQueryBuilder = modal.dataset.frModalQueryBuilder === 'true' ? true : false
		} else {
			isInQueryBuilder = false
		}
	}

	function generateModalTriggerConnection() {
		const modalsByTrigger = new Map();

		for (let i = 0; i < modalsNotInQueryBuilder.length; i++) {
			const modal = modalsNotInQueryBuilder[i];
			const trigger = modal.dataset.frModalTrigger;
			if (!modalsByTrigger.has(trigger)) {
				modalsByTrigger.set(trigger, new Set());
			}
			modalsByTrigger.get(trigger).add(modal);

			initYoutubeIframeAPI(modal)

		}


		for (const [key, value] of modalsByTrigger) {
			const triggers = document.querySelectorAll(key);
			triggers.forEach((trigger) => {
				const modal = Array.from(value)[0];
				const modalID = modal.id;
				trigger.setAttribute('href', `#${modalID}`);
				trigger.setAttribute('tabindex', '0');
				trigger.setAttribute('role', 'button');
				openModalWithTrigger(trigger)
				focusFirstElementOnOpen(trigger)
				trapFocusInModal(trigger)
				initModalRelativeToTrigger(trigger)

			});
		}
	}




	function initModalRelativeToTrigger(trigger) {
		let windowWidth = window.innerWidth;
		modalRelativeToTrigger(trigger, windowWidth)

		window.addEventListener('resize', () => {
			windowWidth = window.innerWidth;
			modalRelativeToTrigger(trigger, windowWidth)
		});

		window.addEventListener('orientationchange', () => {
			windowWidth = window.innerWidth;
			modalRelativeToTrigger(trigger, windowWidth)
		});

		window.addEventListener('scroll', () => {
			windowWidth = window.innerWidth;
			modalRelativeToTrigger(trigger, windowWidth)
		});
	}

	function modalRelativeToTrigger(trigger, windowWidth) {

		const modal = document.querySelector(trigger.getAttribute('href'));
		const container = modal.querySelector('.fr-modal__body');
		let positionRelatedToTrigger = modal.dataset.frModalPositionRelatedToTrigger;
		let placeFromTriggers = modal.dataset.frModalPlaceFromTriggers;
		let triggerOffset = modal.dataset.frModalTriggerOffset;

		// positionRelatedToTrigger === 'top' || 'bottom'
		// placeFromTriggers === 'left' || 'right' || 'center' || 'full' || 'container'

		if (triggerOffset === '0') {
			triggerOffset = '0px'
		}

		const triggerRect = trigger.getBoundingClientRect();
		const triggerTop = triggerRect.top;
		const triggerLeft = triggerRect.left;
		const triggerWidth = triggerRect.width;
		const triggerHeight = triggerRect.height;
		const containerWidth = container.getBoundingClientRect().width;
		const containerHeight = container.getBoundingClientRect().height;


		if (placeFromTriggers === 'left' && windowWidth - triggerLeft - triggerWidth < containerWidth) {
			placeFromTriggers = 'right'
		}

		if (placeFromTriggers === 'right' && triggerLeft + triggerWidth < containerWidth) {
			placeFromTriggers = 'left'
		}

		if (positionRelatedToTrigger === 'bottom' && window.innerHeight - triggerTop - triggerHeight < containerHeight) {
			positionRelatedToTrigger = 'top'
		}

		if (positionRelatedToTrigger === 'top' && triggerTop < containerHeight) {
			positionRelatedToTrigger = 'bottom'
		}


		container.style.position = 'fixed';

		if (positionRelatedToTrigger === 'bottom') {
			container.style.top = `calc(${triggerTop + triggerHeight}px + ${triggerOffset})`;
			container.style.bottom = '';
		} else if (positionRelatedToTrigger === 'top') {
			container.style.bottom = `calc(${window.innerHeight - triggerTop}px + ${triggerOffset})`;
			container.style.top = '';
		}

		if (placeFromTriggers === 'left') {
			container.style.left = `${triggerLeft}px`;
			container.style.maxWidth = `calc(${windowWidth}px - ${triggerLeft}px)`;
			container.style.right = '';
		} else if (placeFromTriggers === 'right') {
			if (containerHeight > window.innerHeight) {
				container.style.right = `${windowWidth - triggerLeft - triggerWidth}px`;
				container.style.maxWidth = `calc(${windowWidth}px - ${windowWidth - triggerLeft - triggerWidth}px)`;
			} else {
				container.style.right = `${windowWidth - triggerLeft - triggerWidth}px - 16px`;
				container.style.maxWidth = `calc(${windowWidth}px - ${windowWidth - triggerLeft - triggerWidth}px)`;
			}
			container.style.left = '';
		} else if (placeFromTriggers === 'center') {
			container.style.left = `calc(${triggerLeft + triggerWidth / 2}px - ${containerWidth / 2}px)`;
		} else if (placeFromTriggers === 'full') {
			container.style.left = '0';
			container.style.maxWidth = '100%';
		} else if (placeFromTriggers === 'container') {
			container.style.left = '50%';
			container.style.transform = 'translateX(-50%)';
			container.style.maxWidth = 'var(--width-vp-max)';
			container.style.width = '100%';
		}

	}







	// Open Modal Functions

	// function openModalWithTrigger(trigger) {
	// 	trigger.addEventListener('click', (e) => {
	// 		openModal(e, trigger)
	// 		initModalRelativeToTrigger(trigger)
	// 	});
	// 	trigger.addEventListener('keydown', (e) => {
	// 		if (e.key === 'Enter') {
	// 			openModal(e, trigger)
	// 			initModalRelativeToTrigger(trigger)
	// 		}
	// 	});
	// }

	function openModalWithTrigger(trigger) {
    // Check if the trigger already has the event listeners attached
    if (!trigger.hasEventListener) {
        trigger.addEventListener('click', (e) => {
            openModal(e, trigger);
            initModalRelativeToTrigger(trigger);
        });
        trigger.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                openModal(e, trigger);
                initModalRelativeToTrigger(trigger);
            }
        });

        // Set flag to indicate event listeners are attached
        trigger.hasEventListener = true;
    }

    // Get the modal associated with this trigger
    const modalId = trigger.getAttribute('href');
    const modal = document.querySelector(modalId);

    // Stop click events inside the modal from bubbling up to the trigger
    if (modal) {
        const modalBody = modal.querySelector('.fr-modal__body');
        if (modalBody) {
            modalBody.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
    }
}




	function openModal(e, trigger) {
		console.log('openModal')
		e.preventDefault();
		const modalId = trigger.getAttribute('href');
		const modal = document.querySelector(modalId);
		if (!modal.closest('header')) {
			modal.classList.add('fr-modal--open');

		} else {
			modal.classList.toggle('fr-modal--open');
		}
		modal.setAttribute('aria-hidden', 'false');

		if ('ontouchstart' in window) {
			scrollingToggle();
		} else {
			scrollingToggle();
		}

		if (isTriggerAHamburgerWidget(trigger)) {
			trigger.classList.toggle(higherIndexTriggerClass);
		}

		if (Boolean(modal.dataset.frModalVideoAutoplay) && !youtubeApiInitiated) {
			startVideoOnModalOpen(modal)
			console.log('modal.dataset.frVideoAutoplay', modal.dataset.frVideoAutoplay)
			youtubeApiInitiated = true;
		}
	}



	function closeModalWithHamburgerWidget(modals) {
		modals.forEach(modal => {
			const trigger = document.querySelector(`[href="#${modal.id}"]`);
			if (isTriggerAHamburgerWidget(trigger)) {
				trigger.addEventListener('click', (e) => {
					if (modal.classList.contains('fr-modal--open') && !trigger.classList.contains(higherIndexTriggerClass)) {
						closeModal(modal, e)
					}
				});
				trigger.addEventListener('keydown', (e) => {
					if (e.key === 'Enter') {
						if (modal.classList.contains('fr-modal--open') && !trigger.classList.contains(higherIndexTriggerClass)) {
							closeModal(modal, e)
						}
					}
				});
			}
		});
	}


	function closeModalWithCloseIcon(modals) {
		modals.forEach(modal => {
			if (modal.querySelector('.fr-modal__close-icon-wrapper')) {
				const fadeTime = Number(modal.dataset.frModalFadeTime);
				const closeIcon = modal.querySelector('.fr-modal__close-icon-wrapper');
				closeIcon.addEventListener('click', (e) => {
					closeModal(modal, e)
					removeActiveBurgerClassOnModalClose(modal)
				});
				closeIcon.addEventListener('keydown', (e) => {
					if (e.key === 'Enter') {
						closeModal(modal, e)
						removeActiveBurgerClassOnModalClose(modal)
					}
				});
			}
		});

	}

	function closeModalWithEscKey(modals) {
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') {
				modals.forEach(modal => {
					const fadeTime = Number(modal.dataset.frModalFadeTime);
					if (modal.classList.contains('fr-modal--open')) {
						closeModal(modal, e)
						removeActiveBurgerClassOnModalClose(modal)
					}
				});
			}


		});
	}

	function closeModalWithACustomSelector(modals) {
		modals.forEach(modal => {
			getDataAttributesForModal(modal)
			if (modal.querySelector(closeSelector)) {
				const closeElement = modal.querySelector(closeSelector);
				closeElement.addEventListener('click', (e) => {
					closeModal(modal, e)
					removeActiveBurgerClassOnModalClose(modal)
				});
			}
		});
	}




	function closeModalOnOverlayClick(modals) {
		modals.forEach(modal => {
			const fadeTime = Number(modal.dataset.frModalFadeTime);
			const overlay = modal.querySelector('.fr-modal__overlay');
			overlay.addEventListener('click', (e) => {
				// e.stopPropagation(); // Stop event from bubbling up
				console.log('overlay click start ')
				closeModal(modal, e)
				removeActiveBurgerClassOnModalClose(modal)
				console.log('overlay click end')
			});
		});
	}




	function closeModal(modal, closeTrigger) {
		if (closeTrigger) {
		closeTrigger.stopPropagation()
		}
		setFocusBackToTrigger(modal)
		// stopVideoOnModalClose()
		modal.classList.remove('fr-modal--open');
		modal.setAttribute('aria-hidden', 'true');
		const currentScrollPosition = window.scrollY;
		setTimeout(() => {
			scrollingToggle()
			window.scrollTo(0, currentScrollPosition);
		}, fadeTime);
	}



	function initYoutubeIframeAPI(modal) {
		if (modal.querySelector('iframe')) {
			window.onYouTubeIframeAPIReady = function () {
				onYouTubeIframeAPIReady();
			};
		}
	}


	function startVideoOnModalOpen(modal) {
		const iframes = modal.querySelectorAll('iframe');

    iframes.forEach(function(iframe) {
        if (youtubePlayers[iframe.id] instanceof YT.Player) {
            youtubePlayers[iframe.id].playVideo();
        }
    });
	}


	function onYouTubeIframeAPIReady() {

		const modals = document.querySelectorAll('.fr-modal');

		modals.forEach(modal => {

		const iframes = modal.querySelectorAll('iframe');

		iframes.forEach(function(iframe) {
				if (!iframe.id) {
					iframe.id = 'frames-iframe-' + Math.random().toString(36).substr(2, 9);
				}
				youtubePlayers[iframe.id] = new YT.Player(iframe.id);
				console.log('Player');
				console.log(youtubePlayers);
				console.log('youtubePlayers');
				console.log(youtubePlayers[iframe.id]);
			});
		});
	}




	function setFocusBackToTrigger(modal) {
		const modalId = modal.id;
		const modalTrigger = document.querySelector(`[href="#${modalId}"]`);
		setTimeout(() => {
			modalTrigger.focus();
		}, fadeTime);
	}

	function stopVideoOnModalClose() {
    for (let player in youtubePlayers) {
        if (youtubePlayers.hasOwnProperty(player)) {
            youtubePlayers[player].stopVideo();
        }
    }
}


	// Global Modal Functions

	function setTransitionTime(modals) {
		modals.forEach(modal => {
			const fadeTime = Number(modal.dataset.frModalFadeTime);
			const modalBody = modal.querySelector('.fr-modal__body');
			modal.style.transition = `all ${fadeTime}ms ease-in-out`;
			modalBody.style.transition = `all ${fadeTime}ms ease-in-out`;
		});
	}

	// Focus Functions

	function focusFirstElementOnOpen(trigger) {
		// trigger.addEventListener('click', (e) => {
		//   focusFirstElementInModal(e, trigger)
		// });
		trigger.addEventListener('keydown', (e) => {
			if (e.key === 'Enter') {
				e.preventDefault();
				focusFirstElementInModal(e, trigger)
			}
		});
	}

	function focusFirstElementInModal(e, trigger) {
		e.preventDefault();
		const modalId = trigger.getAttribute('href');
		const modal = document.querySelector(modalId);
		const modalBody = modal.querySelector('.fr-modal__body');
		const fadeTime = Number(modal.dataset.frModalFadeTime);
		if (modalBody.querySelector(focusableElements)) {
			const firstFocusableElement = modalBody.querySelector(focusableElements);
			if (fadeTime >= 100) {
				setTimeout(() => {
					firstFocusableElement.focus();
				}, fadeTime);
			} else {
				setTimeout(() => {
					firstFocusableElement.focus();
				}, 100);
			}
		}
	}

	function trapFocusInModal(trigger) {

		function focusFirstElementInModal(e, trigger) {
			e.preventDefault();
			const modalId = trigger.getAttribute('href');
			const modal = document.querySelector(modalId);
			const firstFocusableElement = modal.querySelector(focusableElements);
			const focusableContent = modal.querySelectorAll(focusableElements);
			const focusableContentArray = Array.from(focusableContent);
			const lastFocusableElement = focusableContentArray[focusableContentArray.length - 1];

			if (trigger.classList.contains(higherIndexTriggerClass)) {
				const focusableAndTrigger = [trigger, ...focusableContentArray];
				const firstFocusableElement = focusableAndTrigger[0];
				const lastFocusableElement = focusableAndTrigger[focusableAndTrigger.length - 1];
				loopThroughFocusElements(firstFocusableElement, lastFocusableElement)
			} else {
				loopThroughFocusElements(firstFocusableElement, lastFocusableElement)
			}
		}

		trigger.addEventListener('click', (e) => {
			focusFirstElementInModal(e, trigger)
		});
		trigger.addEventListener('keydown', (e) => {
			if (e.key === 'Enter') {
				focusFirstElementInModal(e, trigger)
			}
		});
	}




	// Focus Functions Helpers

	function loopThroughFocusElements(firstFocusableElement, lastFocusableElement) {
		firstFocusableElement.addEventListener('keydown', (e) => {
			if (e.key === 'Tab' && e.shiftKey) {
				e.preventDefault();
				lastFocusableElement.focus();
			}
		});

		lastFocusableElement.addEventListener('keydown', (e) => {
			if (e.key === 'Tab' && !e.shiftKey) {
				e.preventDefault();
				firstFocusableElement.focus();
			}
		});
	}

	function preventScrolling() {
		document.body.style.overflow = 'hidden';
	}



	const scrollBarWidth = window.innerWidth - document.documentElement.clientWidth;
	document.documentElement.style.setProperty('--fr-scrollbar-width', `${scrollBarWidth}px`);

	function scrollingToggle() {
		const bodyHeight = document.body.scrollHeight;
		const windowHeight = window.innerHeight;
		if (bodyHeight > windowHeight) {
			if (window.innerWidth > 767 && scrollBarWidth > 0) {
				document.body.classList.toggle('fr-modal-body-padding');
			}
		}
		document.body.classList.toggle('fr-modal-prevent-scroll')
	}

	function removeAllInBuilder() {
		const modals = document.querySelectorAll('.iframe .fr-modal');
		modalsInQueryBuilder.forEach(modal => {
			if (modal !== modals[0]) {
				modal.style.display = 'none';
			}
			modals[0].style.display = 'flex';
		});
	}

	function setModalBodyToOverflowScroll(modal) {
		const modalBody = modal.querySelector('.fr-modal__body');
		if (modal.dataset.frModalScroll === 'true') {
			modalBody.style.overflow = 'scroll';
		}
		else {
			return
		}
	}


	function removeActiveBurgerClassOnModalClose(modal) {
		const trigger = document.querySelector(`[href="#${modal.id}"]`);
		if (isTriggerAHamburgerWidget(trigger)) {
			if (trigger.classList.contains(higherIndexTriggerClass)) {
				trigger.classList.remove(higherIndexTriggerClass);
				// if trigger containe fr-hamburger--active
				if (trigger.classList.contains('fr-hamburger--active')) {
					trigger.classList.remove('fr-hamburger--active');
				}
				if (trigger.classList.contains('fr-button-trigger--active')) {
					let options = {};
					if (trigger.dataset.frTriggerOptions) {
						try {
								options = JSON.parse(trigger.dataset.frTriggerOptions);
						} catch (error) {
								console.error('Error parsing frTriggerOptions', error);
						}
					}
					console.log('options', options)
					if (options.useActiveText) {

							trigger.querySelector(".fr-button-trigger__text").innerHTML = options.buttonText;

					}
					trigger.classList.remove('fr-button-trigger--active');
				}
			}
		}
	}

	function isTriggerAHamburgerWidget(trigger) {

		if (trigger) {
			if (trigger.classList.contains('brxe-fr-trigger')) {
				return true
			} else {
				return false
			}
		}
	}

	function showOnlyFirstModalInBuilderInsideQuery() {
		const modals = document.querySelectorAll('.iframe .fr-modal');
		modals.forEach((modal, index) => {

			if (modal.dataset.frModalInsideQuery === 'true' && index !== 0) {
				modal.remove()
			}
		})
	}

	// Logic



	for (let i = 0; i < modals.length; i++) {
		const modal = modals[i];
		modal.classList.remove('fr-modal--hide');
	}

	for (let i = 0; i < modals.length; i++) {
		const modal = modals[i];
		getDataAttributesForModal(modal)
	}


	// function generateModalTriggerConnectionInQueryBuilder() {
	// 	const modalsIDs = [];
	// 	modalsInQueryBuilder.forEach((modal, index) => {
	// 		const triggerSelector = modal.dataset.frModalTrigger;
	// 		modalsIDs.push(modal.id);
	// 		const triggers = document.querySelectorAll(triggerSelector);
	// 		triggers.forEach((trigger, index) => {
	// 			let sharedParent = trigger.parentElement;

	// 			while (sharedParent !== null && !modal.parentElement.contains(sharedParent)) {
	// 				sharedParent = sharedParent.parentElement;
	// 			}
	// 			if (sharedParent) {
	// 				const innerModal = sharedParent.querySelector('.fr-modal');
	// 				const innerTrigger = sharedParent.querySelector(`${innerModal.dataset.frModalTrigger}`);
	// 				let newID
	// 				if (document.querySelectorAll(`#${innerModal.id}`).length > 1) {
	// 					newID = `${innerModal.id}-${Math.random().toString(36).substr(2, 5)}`;
	// 				} else {
	// 					newID = innerModal.id;
	// 				}
	// 				innerModal.id = newID;
	// 				innerTrigger.setAttribute('href', `#${innerModal.id}`);
	// 				if (innerModal.id === innerTrigger.getAttribute('href').slice(1)) {
	// 					innerTrigger.setAttribute('tabindex', '0');
	// 					innerTrigger.setAttribute('role', 'button');
	// 					openModalWithTrigger(innerTrigger);
	// 					focusFirstElementOnOpen(innerTrigger);
	// 					trapFocusInModal(innerTrigger);
	// 				}
	// 			} else {
	// 				// Custom console warning
	// 				console.warn(`Modal and trigger do not share the same parent. Modal ID: ${modal.id}, Trigger ID: ${trigger.id}`);
	// 			}
	// 		});
	// 		initYoutubeIframeAPI(modal)
	// 	});
	// }

	function generateModalTriggerConnectionInQueryBuilder() {
    const modalsIDs = [];
    modalsInQueryBuilder.forEach((modal, index) => {
        const triggerSelector = modal.dataset.frModalTrigger;
        modalsIDs.push(modal.id);
        const triggers = document.querySelectorAll(triggerSelector);
        triggers.forEach((trigger, index) => {
            let sharedParent = trigger.closest(`.${modal.dataset.frModalQueryId}`);

            if (sharedParent) {
                let innerModal, innerTrigger;
                if (sharedParent === trigger) {
                    // Trigger is the sharedParent itself
                    innerModal = sharedParent.querySelector('.fr-modal');
                    innerTrigger = trigger;
                } else {
                    innerModal = sharedParent.querySelector('.fr-modal');
                    innerTrigger = sharedParent.querySelector(`${innerModal.dataset.frModalTrigger}`);
                }

                let newID
                if (document.querySelectorAll(`#${innerModal.id}`).length > 1) {
                    newID = `${innerModal.id}-${Math.random().toString(36).substr(2, 5)}`;
                } else {
                    newID = innerModal.id;
                }
                innerModal.id = newID;
                innerTrigger.setAttribute('href', `#${innerModal.id}`);
                if (innerModal.id === innerTrigger.getAttribute('href').slice(1)) {
                    innerTrigger.setAttribute('tabindex', '0');
                    innerTrigger.setAttribute('role', 'button');
                    openModalWithTrigger(innerTrigger);
                    focusFirstElementOnOpen(innerTrigger);
                    trapFocusInModal(innerTrigger);
                }
            } else {
                // Custom console warning
                console.warn(`Modal and trigger do not share the same parent. Modal ID: ${modal.id}, Trigger ID: ${trigger.id}`);
            }
        });
        initYoutubeIframeAPI(modal)
    });
}



	const modalsInQueryBuilder = Array.from(modals).filter(modal => modal.dataset.frModalInsideQuery === 'true')
	const modalsNotInQueryBuilder = Array.from(modals).filter(modal => modal.dataset.frModalInsideQuery === 'false')


	generateModalTriggerConnection()
	generateModalTriggerConnectionInQueryBuilder()

	// modals.forEach(modal => {
	//   setModalBodyToOverflowScroll(modal)
	// });



	setTransitionTime(modals)

	// Close Modal Functions
	closeModalWithHamburgerWidget(modals)
	closeModalWithEscKey(modals)
	if (!document.querySelector('.iframe .fr-modal')) {
		closeModalOnOverlayClick(modals)
		closeModalWithACustomSelector(modals)
		closeModalWithCloseIcon(modals)
	}

	// Builder Functions
	showOnlyFirstModalInBuilderInsideQuery()

}


function wpgb_modal_script() {
	window.WP_Grid_Builder && WP_Grid_Builder.on('init', function (wpgb) {

		console.dir(wpgb); // Holds all instances.

		wpgb.facets.on('appended', function (nodes) {

			modal_script()

		});

	});
}

document.addEventListener("DOMContentLoaded", function (e) {
	bricksIsFrontend && modal_script();
	bricksIsFrontend && wpgb_modal_script();
});
