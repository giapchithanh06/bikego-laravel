/**
 * BikeGo - rental.js  (pages /bike-rental and /bike-return)
 *  - Ward dropdown -> station dropdown loaded by AJAX
 *    (rent: stations that still have a bike, return: stations that still have a free dock)
 *  - Live minutes / amount of the rental in progress
 */
(function () {
  'use strict';

  var BASE = (document.body.getAttribute('data-base-url') || '').replace(/\/$/, '');

  // ---------- Ward -> station dropdowns ----------
  document.querySelectorAll('select[data-purpose]').forEach(function (ward) {
    var station = document.querySelector(ward.getAttribute('data-target'));
    var purpose = ward.getAttribute('data-purpose');
    var previous = ward.getAttribute('data-old-station'); // keeps the choice after a validation error

    if (!station) {
      return;
    }

    /** Replace the options of the station dropdown. */
    function fill(placeholder, stations) {
      station.textContent = '';
      station.add(new Option(placeholder, ''));
      stations.forEach(function (item) {
        station.add(new Option(item.label, item.id));
      });
    }

    function load() {
      if (!ward.value) {
        fill('Chọn Trạm', []);
        return;
      }

      fill('Đang tải...', []);

      fetch(BASE + '/api/wards/' + encodeURIComponent(ward.value) + '/stations?purpose=' + purpose, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin'
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (result) {
          fill(result.data.length ? 'Chọn Trạm' : 'Không có trạm phù hợp', result.data);
          if (previous) {
            station.value = previous;
          }
        })
        .catch(function () {
          fill('Không tải được danh sách trạm', []);
        });
    }

    ward.addEventListener('change', function () {
      previous = '';
      load();
    });

    if (ward.value) {
      load();
    }
  });

  // ---------- Live minutes and amount ----------
  var box = document.getElementById('active-rental');

  if (box) {
    var start = Number(box.getAttribute('data-start'));
    var price = Number(box.getAttribute('data-price'));
    // Difference between the server clock and the browser clock
    var offset = Number(box.getAttribute('data-now')) - Date.now();
    var minutesEl = box.querySelector('[data-role="minutes"]');
    var amountEl = box.querySelector('[data-role="amount"]');

    var tick = function () {
      var seconds = Math.max(0, (Date.now() + offset - start) / 1000);
      var minutes = Math.max(1, Math.ceil(seconds / 60)); // same rounding as the server
      minutesEl.textContent = minutes;
      amountEl.textContent = (minutes * price).toLocaleString('vi-VN');
    };

    setInterval(tick, 10000);
  }
})();
