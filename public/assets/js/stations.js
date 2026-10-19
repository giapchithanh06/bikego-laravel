/**
 * BikeGo - stations.js  (page /stations)
 * Choose a ward -> list its stations by AJAX (no page reload).
 * Click a station name -> accordion opens and loads the detail by AJAX.
 */
(function () {
  'use strict';

  var B = window.BikeGo;
  var wardSelect = document.getElementById('ward');
  var list = document.getElementById('station-list');

  if (!wardSelect || !list || !B) {
    return;
  }

  /** Replace the list content with a single message. */
  function showMessage(text) {
    list.textContent = '';
    list.appendChild(B.el('p', 'empty-note', text));
  }

  /** Build one accordion item for a station. */
  function buildItem(station) {
    var item = B.el('details', 'acc-item');
    var head = B.el('summary', 'acc-head');

    // Title: "Station name (bikes - free docks)"
    var title = B.el('span', 'acc-title');
    title.appendChild(B.icon('geo-alt'));
    title.appendChild(B.el('span', 'acc-name', ' ' + station.name + ' '));
    title.appendChild(document.createTextNode('('));
    title.appendChild(B.bikesChip(station));
    title.appendChild(document.createTextNode(' - '));
    title.appendChild(B.docksChip(station));
    title.appendChild(document.createTextNode(')'));
    head.appendChild(title);

    var right = B.el('span', 'acc-right');
    right.appendChild(B.statusBadge(station));
    right.appendChild(B.icon('chevron-down chevron'));
    head.appendChild(right);

    var body = B.el('div', 'acc-body');
    item.appendChild(head);
    item.appendChild(body);

    // Accordion: only one item open at a time, detail is loaded the first time it opens
    item.addEventListener('toggle', function () {
      if (!item.open) {
        return;
      }
      list.querySelectorAll('details.acc-item[open]').forEach(function (other) {
        if (other !== item) {
          other.open = false;
        }
      });
      if (!item.getAttribute('data-loaded')) {
        item.setAttribute('data-loaded', '1');
        B.loadDetail(station.id, body);
      }
    });

    return item;
  }

  wardSelect.addEventListener('change', function () {
    if (!wardSelect.value) {
      showMessage('Hãy chọn phường để xem danh sách trạm xe.');
      return;
    }

    showMessage('Đang tải danh sách trạm...');

    B.api('/api/stations?ward_id=' + encodeURIComponent(wardSelect.value))
      .then(function (result) {
        list.textContent = '';
        if (result.data.length === 0) {
          showMessage('Phường này chưa có trạm xe nào.');
          return;
        }
        result.data.forEach(function (station) {
          list.appendChild(buildItem(station));
        });
      })
      .catch(function () {
        showMessage('Không tải được danh sách trạm. Vui lòng thử lại.');
      });
  });
})();
