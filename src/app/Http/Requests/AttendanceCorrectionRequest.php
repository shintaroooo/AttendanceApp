<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'work_date' => ['required', 'date'],

            'clock_in_at' => ['required'],
            'clock_out_at' => ['required', 'after:clock_in_at'],

            'reason' => ['required'],
            'break_start_0' => ['nullable', 'before:clock_out_at'],
            'break_end_0' => ['nullable', 'before_or_equal:clock_out_at'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn = $this->clock_in_at;
            $clockOut = $this->clock_out_at;

            foreach ($this->all() as $key => $value) {

            //break_start_x
                if (str_contains($key, 'break_start_')) {
                    $index = str_replace('break_start_', '', $key);
                    $endKey = "break_end_{$index}";

                    $start = $value;
                    $end = $this->input($endKey);
                    //①start > end
                    if ($start && $end && $start >= $end) {
                        $validator->errors()->add($endKey, '休憩時間が不適切な値です');
                    }
                    //②勤務時間外
                    if ($start && ($start < $clockIn || $start > $clockOut)) {
                        $validator->errors()->add($key, '休憩開始時間が勤務時間外です');
                    }
                    if ($end && ($end < $clockIn || $end > $clockOut)) {
                        $validator->errors()->add($endKey, '休憩時間が勤務時間外です');
                    }
                }
            }

            //出勤 > 退勤
            if ($clockIn >= $clockOut) {
                $validator->errors()->add('clock_in_at', '出勤時間もしくは退勤時間が不適切な値です');
            }
        });
    }

    public function messages()
    {
        return [
            'clock_in_at.required' => '出勤時間を入力してください',
            'clock_out_at.required' => '退勤時間を入力してください',
            'clock_out_at.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',
        ];
    }
}