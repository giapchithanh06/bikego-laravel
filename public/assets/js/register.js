/**
 * BikeGo - register.js  (page /register)
 * Client-side validation, shown under each field while the user types.
 * The same rules are checked again on the server (App\Http\Requests\RegisterRequest).
 */
(function () {
  'use strict';

  var form = document.getElementById('register-form');
  if (!form) {
    return;
  }

  // Vietnamese letters belong to the Latin script; \p{M} allows combining accents
  var NAME_REGEX = /^[\p{Script=Latin}\p{M}]+(?: [\p{Script=Latin}\p{M}]+)*$/u;
  var PHONE_REGEX = /^(?:0\d{9}|84\d{9}|\+84\d{9})$/;
  var EMAIL_REGEX = /^(?!\.)(?!.*\.\.)(?!.*\.@)[a-z0-9.]{6,30}@gmail\.com$/;

  /** Each validator returns an error message, or '' when the value is valid. */
  var validators = {
    name: function (value) {
      if (value === '') {
        return 'Vui lòng nhập họ tên.';
      }
      if (!NAME_REGEX.test(value)) {
        return 'Họ tên chỉ gồm chữ cái, không có 2 khoảng trắng liên tiếp và không bắt đầu/kết thúc bằng khoảng trắng.';
      }
      if ((value.match(/\p{Script=Latin}/gu) || []).length < 3) {
        return 'Họ tên phải chứa tối thiểu 3 ký tự chữ cái.';
      }
      return '';
    },

    phone: function (value) {
      if (value === '') {
        return 'Vui lòng nhập số điện thoại.';
      }
      return PHONE_REGEX.test(value) ? '' : 'Số điện thoại gồm 10 số bắt đầu bằng 0, hoặc 11 số bắt đầu bằng 84 (hoặc +84).';
    },

    email: function (value) {
      if (value === '') {
        return 'Vui lòng nhập email.';
      }
      return EMAIL_REGEX.test(value)
        ? ''
        : 'Email phải có dạng tên@gmail.com (tên 6-30 ký tự a-z, 0-9 hoặc dấu chấm, không bắt đầu/kết thúc bằng dấu chấm, không có 2 dấu chấm liên tiếp).';
    },

    password: function (value) {
      if (value === '') {
        return 'Vui lòng nhập mật khẩu.';
      }
      if (/[^\x20-\x7E]/.test(value)) {
        return 'Mật khẩu không được chứa ký tự tiếng Việt có dấu.';
      }
      if (value.length < 8) {
        return 'Mật khẩu phải có ít nhất 8 ký tự.';
      }
      if (!/[A-Z]/.test(value) || !/[a-z]/.test(value) || !/[0-9]/.test(value) || !/[!@#$%^&*]/.test(value)) {
        return 'Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường, 1 chữ số và 1 ký tự đặc biệt (!@#$%^&*).';
      }
      return '';
    },

    password_confirmation: function (value) {
      if (value === '') {
        return 'Vui lòng nhắc lại mật khẩu.';
      }
      return value === form.elements.password.value ? '' : 'Mật khẩu nhắc lại không khớp.';
    }
  };

  /** Validate one field and show / clear its error. Returns true when valid. */
  function check(input) {
    var name = input.getAttribute('data-field');
    var message = validators[name](input.value);
    var target = form.querySelector('[data-error-for="' + name + '"]');

    if (target) {
      target.textContent = message;
    }
    input.classList.toggle('invalid', message !== '');
    return message === '';
  }

  var inputs = Array.prototype.slice.call(form.querySelectorAll('[data-field]'));

  // Validate while typing
  inputs.forEach(function (input) {
    input.addEventListener('input', function () {
      check(input);
      // The confirmation depends on the password, so re-check it too
      if (input.getAttribute('data-field') === 'password' && form.elements.password_confirmation.value !== '') {
        check(form.elements.password_confirmation);
      }
    });
  });

  // Block the submit when something is wrong
  form.addEventListener('submit', function (event) {
    var firstInvalid = null;

    inputs.forEach(function (input) {
      if (!check(input) && !firstInvalid) {
        firstInvalid = input;
      }
    });

    if (firstInvalid) {
      event.preventDefault();
      firstInvalid.focus();
    }
  });
})();
