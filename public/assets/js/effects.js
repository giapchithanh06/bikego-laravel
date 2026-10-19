/**
 * BikeGo - effects.js
 * Scramble ("random flicker") title effect.
 * Any element with the class .scramble-text is animated once, the first time
 * it scrolls into the viewport. Characters flicker randomly and settle from
 * left to right until the original text is visible.
 */
(function () {
  'use strict';

  var CHARSET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789#@$%&*?';
  var FRAME_MS = 45;     // time between two flicker frames
  var STEP_FRAMES = 2;   // frames before the next character is fixed

  /** Return a random character from the charset. */
  function randomChar() {
    return CHARSET.charAt(Math.floor(Math.random() * CHARSET.length));
  }

  /** Run the scramble animation on one element. */
  function scramble(el) {
    var finalText = el.getAttribute('data-text') || el.textContent;
    var frame = 0;
    el.setAttribute('aria-label', finalText);

    var timer = setInterval(function () {
      var fixed = Math.floor(frame / STEP_FRAMES);
      var output = '';

      for (var i = 0; i < finalText.length; i++) {
        var ch = finalText.charAt(i);
        // Keep spaces and already-fixed characters, flicker the rest
        output += (ch === ' ' || i < fixed) ? ch : randomChar();
      }

      el.textContent = output;
      frame++;

      if (fixed >= finalText.length) {
        clearInterval(timer);
        el.textContent = finalText;
      }
    }, FRAME_MS);
  }

  /** Observe every .scramble-text element and start when it becomes visible. */
  function init() {
    var items = document.querySelectorAll('.scramble-text');

    items.forEach(function (el) {
      el.setAttribute('data-text', el.textContent.trim());
    });

    // Fallback: no IntersectionObserver => show text as-is (no animation)
    if (!('IntersectionObserver' in window)) {
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          scramble(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });

    items.forEach(function (el) {
      observer.observe(el);
    });
  }

  document.addEventListener('DOMContentLoaded', init);
})();
