/**
 * BikeGo - station-detail.js
 * Shared helpers to display a station (used by /stations and by the home page dialog).
 * Everything is built with DOM methods + textContent, so server data can never inject HTML.
 *
 * Public API (window.BikeGo): api, el, icon, statusBadge, bikesChip, docksChip, loadDetail
 */
(function (global) {
  'use strict';

  var BASE = (document.body.getAttribute('data-base-url') || '').replace(/\/$/, '');

  /** GET a JSON endpoint of the application. */
  function api(path) {
    return fetch(BASE + path, {
      headers: { Accept: 'application/json' },
      credentials: 'same-origin'
    }).then(function (response) {
      if (!response.ok) {
        throw new Error('HTTP ' + response.status);
      }
      return response.json();
    });
  }

  /** Create an element with an optional class and text. */
  function el(tag, className, text) {
    var node = document.createElement(tag);
    if (className) {
      node.className = className;
    }
    if (text !== undefined && text !== null) {
      node.textContent = text;
    }
    return node;
  }

  /** Bootstrap Icons element, e.g. icon('bicycle'). */
  function icon(name) {
    var node = document.createElement('i');
    node.className = 'bi bi-' + name;
    return node;
  }

  /** 5 star icons for a rating (0-5). */
  function stars(value) {
    var wrap = el('span', 'stars');
    for (var i = 1; i <= 5; i++) {
      wrap.appendChild(icon(value >= i ? 'star-fill' : (value >= i - 0.5 ? 'star-half' : 'star')));
    }
    return wrap;
  }

  /** Station status: green = active, red = maintenance. */
  function statusBadge(station) {
    var active = station.status === 'active';
    var badge = el('span', 'badge ' + (active ? 'badge-ok' : 'badge-bad'));
    badge.appendChild(icon(active ? 'check-circle-fill' : 'cone-striped'));
    badge.appendChild(document.createTextNode(' ' + station.status_label));
    return badge;
  }

  /** Bikes in the station: good colour when >= 50% of the docks hold a bike. */
  function bikesChip(station) {
    var good = station.total_docks > 0 && station.bikes_present * 2 >= station.total_docks;
    var chip = el('span', 'chip ' + (good ? 'chip-good' : 'chip-bad'));
    chip.appendChild(icon('bicycle'));
    chip.appendChild(document.createTextNode(' ' + station.bikes_present + ' xe'));
    return chip;
  }

  /** Free docks: good colour when >= 50% of the docks are free. */
  function docksChip(station) {
    var good = station.total_docks > 0 && station.empty_docks * 2 >= station.total_docks;
    var chip = el('span', 'chip ' + (good ? 'chip-good' : 'chip-bad'));
    chip.appendChild(icon('p-square'));
    chip.appendChild(document.createTextNode(' ' + station.empty_docks + ' chỗ trống'));
    return chip;
  }

  /** One row of the detail list: icon + label + value node. */
  function line(iconName, label, valueNode) {
    var li = el('li');
    li.appendChild(icon(iconName));
    li.appendChild(document.createTextNode(' ' + label + ' '));
    li.appendChild(valueNode);
    return li;
  }

  /** One review: name, stars, comment, date. */
  function reviewItem(review) {
    var li = el('li');
    li.appendChild(el('strong', null, review.user));
    li.appendChild(document.createTextNode(' '));
    li.appendChild(stars(review.rating));
    li.appendChild(document.createTextNode(' - ' + (review.comment || '(không có bình luận)') + ' '));
    li.appendChild(el('small', 'muted', review.created_at));
    return li;
  }

  /** Fill $container with the detail of a station (address, docks, bikes, newest reviews). */
  function renderDetail(container, station) {
    container.textContent = '';

    var list = el('ul', 'detail-lines');
    list.appendChild(line('signpost-split', 'Địa chỉ:', el('strong', null, station.address + (station.ward ? ', ' + station.ward : ''))));
    list.appendChild(line('p-square', 'Tổng số chỗ:', el('strong', null, String(station.total_docks))));
    list.appendChild(line('p-circle', 'Số chỗ còn trống:', docksChip(station)));
    list.appendChild(line('bicycle', 'Số xe hiện có:', bikesChip(station)));
    list.appendChild(line('activity', 'Tình trạng:', statusBadge(station)));
    container.appendChild(list);

    var heading = el('h4', 'sub-title');
    heading.appendChild(icon('chat-left-text'));
    heading.appendChild(document.createTextNode(' Đánh giá mới nhất'));
    if (station.avg_rating !== null) {
      heading.appendChild(document.createTextNode(' '));
      heading.appendChild(stars(station.avg_rating));
      heading.appendChild(document.createTextNode(' ' + station.avg_rating.toFixed(1) + '/5'));
    }
    container.appendChild(heading);

    var reviews = el('ul', 'review-list');
    station.reviews.forEach(function (review) {
      reviews.appendChild(reviewItem(review));
    });
    container.appendChild(reviews);

    if (station.reviews.length === 0) {
      container.appendChild(el('p', 'muted', 'Chưa có đánh giá nào.'));
    }

    // "Tải thêm": load 5 older reviews each time
    var more = el('button', 'btn btn-small btn-light', 'Tải thêm');
    more.type = 'button';
    more.hidden = !station.has_more;
    more.addEventListener('click', function () {
      more.disabled = true;
      api('/api/stations/' + station.id + '/reviews?offset=' + reviews.children.length)
        .then(function (result) {
          result.data.forEach(function (review) {
            reviews.appendChild(reviewItem(review));
          });
          more.hidden = !result.has_more;
          more.disabled = false;
        })
        .catch(function () {
          more.disabled = false;
        });
    });
    container.appendChild(more);
  }

  /** Load the detail of a station by AJAX and show it inside $container. */
  function loadDetail(stationId, container) {
    container.textContent = '';
    container.appendChild(el('p', 'muted', 'Đang tải...'));

    return api('/api/stations/' + stationId)
      .then(function (result) {
        renderDetail(container, result.data);
      })
      .catch(function () {
        container.textContent = '';
        container.appendChild(el('p', 'field-error', 'Không tải được thông tin trạm. Vui lòng thử lại.'));
      });
  }

  global.BikeGo = {
    api: api,
    el: el,
    icon: icon,
    stars: stars,
    statusBadge: statusBadge,
    bikesChip: bikesChip,
    docksChip: docksChip,
    loadDetail: loadDetail
  };
})(window);
