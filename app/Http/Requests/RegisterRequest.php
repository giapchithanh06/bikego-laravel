<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Server-side validation of the registration form.
 * The same rules are implemented on the client in public/assets/js/register.js.
 */
class RegisterRequest extends FormRequest
{
    /** Letters (Vietnamese diacritics allowed) separated by single spaces. */
    public const NAME_REGEX = '/^[\p{Latin}\p{M}]+(?: [\p{Latin}\p{M}]+)*$/u';

    /** 0xxxxxxxxx (10 digits) or 84xxxxxxxxx / +84xxxxxxxxx (11 digits). */
    public const PHONE_REGEX = '/^(?:0\d{9}|84\d{9}|\+84\d{9})$/';

    /** 6-30 chars of a-z 0-9 and dots, not starting/ending with a dot, no "..", domain gmail.com only. */
    public const EMAIL_REGEX = '/^(?!\.)(?!.*\.\.)(?!.*\.@)[a-z0-9.]{6,30}@gmail\.com$/';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:'.self::NAME_REGEX,
                function (string $attribute, mixed $value, \Closure $fail) {
                    // At least 3 letters in total
                    if (preg_match_all('/\p{Latin}/u', (string) $value) < 3) {
                        $fail('Họ tên phải chứa tối thiểu 3 ký tự chữ cái.');
                    }
                },
            ],
            'phone' => [
                'required',
                'regex:'.self::PHONE_REGEX,
                function (string $attribute, mixed $value, \Closure $fail) {
                    // 0912..., 84912... and +84912... are the same phone number
                    if (User::where('phone', User::normalizePhone((string) $value))->exists()) {
                        $fail('Số điện thoại này đã được đăng ký.');
                    }
                },
            ],
            'email' => [
                'required',
                'regex:'.self::EMAIL_REGEX,
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $value = (string) $value;
                    if (preg_match('/[^\x20-\x7E]/', $value)) {
                        $fail('Mật khẩu không được chứa ký tự tiếng Việt có dấu.');
                    } elseif (
                        ! preg_match('/[A-Z]/', $value)
                        || ! preg_match('/[a-z]/', $value)
                        || ! preg_match('/[0-9]/', $value)
                        || ! preg_match('/[!@#$%^&*]/', $value)
                    ) {
                        $fail('Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường, 1 chữ số và 1 ký tự đặc biệt (!@#$%^&*).');
                    }
                },
            ],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên.',
            'name.regex' => 'Họ tên chỉ gồm chữ cái, không có 2 khoảng trắng liên tiếp và không bắt đầu/kết thúc bằng khoảng trắng.',
            'name.max' => 'Họ tên tối đa 100 ký tự.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại gồm 10 số bắt đầu bằng 0, hoặc 11 số bắt đầu bằng 84 (hoặc +84).',
            'email.required' => 'Vui lòng nhập email.',
            'email.regex' => 'Email phải có dạng tên@gmail.com (tên 6-30 ký tự a-z, 0-9 hoặc dấu chấm, không bắt đầu/kết thúc bằng dấu chấm, không có 2 dấu chấm liên tiếp).',
            'email.unique' => 'Email này đã được đăng ký.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password_confirmation.required' => 'Vui lòng nhắc lại mật khẩu.',
            'password_confirmation.same' => 'Mật khẩu nhắc lại không khớp.',
        ];
    }
}
