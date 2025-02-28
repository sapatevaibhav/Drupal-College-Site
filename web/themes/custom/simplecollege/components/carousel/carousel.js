/**
 * @file
 * Carousel component behavior.
 */
(function (Drupal, once) {
    'use strict';

    /**
     * Carousel behavior.
     */
    Drupal.behaviors.carousel = {
      attach(context, settings) {
        // Use the imported once function
        once('carousel', '.carousel', context).forEach(carousel => {
          // Initialize the carousel.
          new CarouselComponent(carousel);
        });
      }
    };

    /**
     * Carousel component class.
     */
    class CarouselComponent {
      /**
       * Constructs a new carousel instance.
       * @param {HTMLElement} element - The carousel DOM element.
       */
      constructor(element) {
        // Store DOM elements.
        this.carousel = element;
        this.container = element.querySelector('.carousel__container');
        this.slides = Array.from(element.querySelectorAll('.carousel__slide'));
        this.prevBtn = element.querySelector('.carousel__arrow--prev');
        this.nextBtn = element.querySelector('.carousel__arrow--next');
        this.dots = Array.from(element.querySelectorAll('.carousel__dot'));

        // Parse settings from data attributes.
        this.settings = {
          autoplay: JSON.parse(this.carousel.dataset.autoplay || 'true'),
          autoplaySpeed: parseInt(this.carousel.dataset.autoplaySpeed || '5000', 10),
          dots: JSON.parse(this.carousel.dataset.dots || 'true'),
          arrows: JSON.parse(this.carousel.dataset.arrows || 'true'),
          peekPercentage: parseInt(this.carousel.dataset.peekPercentage || '10', 10) // New setting for peek amount
        };

        // Initialize state.
        this.currentIndex = 0;
        this.slideWidth = 80; // 80% width for each slide (showing 20% of prev/next)
        this.slideOffset = 10; // 10% offset on each side for peeking
        this.slideCount = this.slides.length;
        this.autoplayTimer = null;

        // Initialize the carousel.
        this.init();
      }

      /**
       * Initialize the carousel.
       */
      init() {
        // Skip initialization if there's only one slide.
        if (this.slideCount <= 1) {
          return;
        }

        // Apply initial styling to slides based on peek setting
        this.applySlideStyles();

        // Set up event listeners.
        this.addEventListeners();

        // Start autoplay if enabled.
        if (this.settings.autoplay) {
          this.startAutoplay();
        }

        // Set initial slide position.
        this.goToSlide(0);
      }

      /**
       * Apply styling to slides for the peek effect
       */
      applySlideStyles() {
        // Set slide width based on peek percentage
        const slideWidth = 100 - (this.settings.peekPercentage * 2);
        this.slideWidth = slideWidth;
        this.slideOffset = this.settings.peekPercentage;

        this.slides.forEach(slide => {
          slide.style.flex = `0 0 ${slideWidth}%`;
        });
      }

      /**
       * Add event listeners to carousel elements.
       */
      addEventListeners() {
        // Arrow button click events.
        if (this.prevBtn) {
          this.prevBtn.addEventListener('click', () => this.goToPrev());
        }

        if (this.nextBtn) {
          this.nextBtn.addEventListener('click', () => this.goToNext());
        }

        // Navigation dot click events.
        this.dots.forEach((dot, index) => {
          dot.addEventListener('click', () => this.goToSlide(index));
        });

        // Touch events for swipe support.
        this.addTouchEvents();

        // Pause autoplay on hover.
        this.carousel.addEventListener('mouseenter', () => this.pauseAutoplay());
        this.carousel.addEventListener('mouseleave', () => {
          if (this.settings.autoplay) {
            this.startAutoplay();
          }
        });

        // Pause autoplay when page is not visible.
        document.addEventListener('visibilitychange', () => {
          if (document.hidden) {
            this.pauseAutoplay();
          } else if (this.settings.autoplay) {
            this.startAutoplay();
          }
        });

        // Keyboard navigation.
        this.carousel.setAttribute('tabindex', '0');
        this.carousel.addEventListener('keydown', (event) => {
          if (event.key === 'ArrowLeft') {
            this.goToPrev();
          } else if (event.key === 'ArrowRight') {
            this.goToNext();
          }
        });
      }

      /**
       * Add touch events for swipe support.
       */
      addTouchEvents() {
        let startX;
        let endX;
        const threshold = 50; // Minimum distance to detect swipe.

        this.carousel.addEventListener('touchstart', (event) => {
          startX = event.touches[0].clientX;
          this.pauseAutoplay();
        });

        this.carousel.addEventListener('touchmove', (event) => {
          endX = event.touches[0].clientX;
        });

        this.carousel.addEventListener('touchend', () => {
          if (startX && endX) {
            const diff = startX - endX;
            if (Math.abs(diff) > threshold) {
              if (diff > 0) {
                this.goToNext();
              } else {
                this.goToPrev();
              }
            }
          }
          if (this.settings.autoplay) {
            this.startAutoplay();
          }
          startX = null;
          endX = null;
        });
      }

      /**
       * Start autoplay timer.
       */
      startAutoplay() {
        this.pauseAutoplay();
        this.autoplayTimer = setInterval(() => {
          this.goToNext();
        }, this.settings.autoplaySpeed);
      }

      /**
       * Pause autoplay timer.
       */
      pauseAutoplay() {
        if (this.autoplayTimer) {
          clearInterval(this.autoplayTimer);
          this.autoplayTimer = null;
        }
      }

      /**
       * Go to the previous slide.
       */
      goToPrev() {
        const newIndex = (this.currentIndex - 1 + this.slideCount) % this.slideCount;
        this.goToSlide(newIndex);
      }

      /**
       * Go to the next slide.
       */
      goToNext() {
        const newIndex = (this.currentIndex + 1) % this.slideCount;
        this.goToSlide(newIndex);
      }

      /**
       * Go to a specific slide by index.
       * @param {number} index - The slide index to go to.
       */
      goToSlide(index) {
        // Calculate the position with peek offset
        const position = index * this.slideWidth;
        const offset = this.slideOffset;

        // Update the transform to move to the new slide, accounting for the peek effect
        this.container.style.transform = `translateX(-${position}%)`;

        // Update active dot.
        if (this.dots.length) {
          this.dots[this.currentIndex].classList.remove('carousel__dot--active');
          this.dots[index].classList.add('carousel__dot--active');
        }

        // Update current index.
        this.currentIndex = index;

        // Announce slide change for screen readers.
        this.announceSlideChange();
      }

      /**
       * Announce slide change for accessibility.
       */
      announceSlideChange() {
        // Find or create the live region for announcements.
        let liveRegion = this.carousel.querySelector('.carousel__live-region');

        if (!liveRegion) {
          liveRegion = document.createElement('div');
          liveRegion.className = 'carousel__live-region visually-hidden';
          liveRegion.setAttribute('aria-live', 'polite');
          liveRegion.setAttribute('aria-atomic', 'true');
          this.carousel.appendChild(liveRegion);
        }

        // Update the announcement.
        liveRegion.textContent = `Slide ${this.currentIndex + 1} of ${this.slideCount}`;
      }
    }

  })(Drupal, once);
