/**
 * BikeGo - app.js
 * Small helpers used on every page:
 *  - confirmation dialog for elements with a data-confirm attribute ("Hủy" / "Đồng ý")
 *  - "select all" checkbox of the trash pages
 *  - character counter for textareas with data-counter
 */
(function () {
  'use strict';

  var dialog = document.getElementById('confirm-dialog');
  var messageEl = document.getElementById('confirm-message');
  var okButton = document.getElementById('confirm-ok');
  var cancelButton = document.getElementById('confirm-cancel');
  var onConfirm = null;      // function called when the user clicks "Đồng ý"
  var confirmedForms = new WeakSet(); // forms that already got an approval

  /** Show the confirmation dialog (falls back to window.confirm on old browsers). */
  function askConfirm(message, callback) {
    if (!dialog || typeof dialog.showModal !== 'function') {
      if (window.confirm(message)) {
        callback();
      }
      return;
    }
    messageEl.textContent = message;
    onConfirm = callback;
    dialog.showModal();
  }

  if (dialog) {
    okButton.addEventListener('click', function () {
      var callback = onConfirm;
      onConfirm = null;
      dialog.close();
      if (callback) {
        callback();
      }
    });

    cancelButton.addEventListener('click', function () {
      onConfirm = null;
      dialog.close();
    });

    // Pressing Esc counts as "Hủy"
    dialog.addEventListener('cancel', function () {
      onConfirm = null;
    });
  }

  // Buttons with data-confirm: ask first, then submit the form with that button
  document.addEventListener('click', function (event) {
    var button = event.target.closest('button[data-confirm]');
    if (!button || !button.form) {
      return;
    }
    event.preventDefault();
    askConfirm(button.getAttribute('data-confirm'), function () {
      button.form.requestSubmit(button);
    });
  });

  // Forms with data-confirm: ask before the form is sent
  document.addEventListener('submit', function (event) {
    var form = event.target;
    if (!form.matches || !form.matches('form[data-confirm]') || confirmedForms.has(form)) {
      return;
    }
    event.preventDefault();
    askConfirm(form.getAttribute('data-confirm'), function () {
      confirmedForms.add(form);
      form.requestSubmit(event.submitter || undefined);
    });
  });

  // "Select all" checkbox in the trash tables
  var checkAll = document.getElementById('check-all');
  if (checkAll) {
    checkAll.addEventListener('change', function () {
      document.querySelectorAll('.row-check').forEach(function (box) {
        box.checked = checkAll.checked;
      });
    });
  }

  // Remaining characters counter for <textarea data-counter maxlength="200">
  document.querySelectorAll('textarea[data-counter]').forEach(function (area) {
    var counter = document.createElement('small');
    counter.className = 'muted counter';
    area.insertAdjacentElement('afterend', counter);

    var update = function () {
      counter.textContent = area.value.length + '/' + area.maxLength;
    };
    area.addEventListener('input', update);
    update();
  });
})();
