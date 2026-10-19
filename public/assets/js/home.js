/**
 * BikeGo - home.js  (home page)
 *  - Top Stations carousel: runs by itself, 4 stations per view (fewer on small screens)
 *  - Click a station name: its detail opens in a dialog (same content as the accordion)
 */
(function () {
  'use strict';

  // ---------- Carousel ----------
  var root = document.getElementById('station-carousel');

  if (root) {
    var track = root.querySelector('.carousel-track');
    var items = track.children;
    var index = 0;
    var timer = null;
    var DELAY = 3500; // ms between two automatic moves

    /** How many cards are visible: 4 on desktop, 2 on tablet, 1 on phone. */
    function perView() {
      if (window.innerWidth <= 720) {
        return 1;
      }
      return window.innerWidth <= 900 ? 2 : 4;
    }

    function maxIndex() {
      return Math.max(0, items.length - perView());
    }

    function show() {
      track.style.transform = 'translateX(' + (-index * (100 / perView())) + '%)';
    }

    function next() {
      index = index >= maxIndex() ? 0 : index + 1;
      show();
    }

    function prev() {
      index = index <= 0 ? maxIndex() : index - 1;
      show();
    }

    function start() {
      stop();
      timer = setInterval(next, DELAY);
    }

    function stop() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    root.querySelectorAll('.carousel-arrow').forEach(function (button) {
      button.addEventListener('click', function () {
        if (button.getAttribute('data-dir') === '-1') {
          prev();
        } else {
          next();
        }
        start(); // restart the timer after a manual move
      });
    });

    // Pause while the mouse is over the carousel
    root.addEventListener('mouseenter', stop);
    root.addEventListener('mouseleave', start);

    window.addEventListener('resize', function () {
      index = Math.min(index, maxIndex());
      show();
    });

    show();
    start();
  }

  // ---------- Station detail dialog ----------
  var dialog = document.getElementById('station-dialog');
  var body = document.getElementById('station-dialog-body');
  var closeButton = document.getElementById('station-dialog-close');

  if (dialog && body && window.BikeGo && typeof dialog.showModal === 'function') {
    document.querySelectorAll('.station-link').forEach(function (button) {
      button.addEventListener('click', function () {
        dialog.showModal();
        window.BikeGo.loadDetail(button.getAttribute('data-station-id'), body);
      });
    });

    closeButton.addEventListener('click', function () {
      dialog.close();
    });

    // Click on the dark backdrop closes the dialog
    dialog.addEventListener('click', function (event) {
      if (event.target === dialog) {
        dialog.close();
      }
    });
  }
})();
