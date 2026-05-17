<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
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
            'clock_in_at' => ['required'],
            'clock_out_at' => ['required'],
            'note' => ['required'],

            'breaks.*.start' => ['nullable'],
            'breaks.*.end' => ['nullable'],
        ];
    }
    public function messages()
    {
        return [
            'clock_in_at.required' => '出勤時間を入力してください',
            'clock_out_at.required' => '退勤時間を入力してください',
            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator){

        //出勤>退勤
        if (
            $this->clock_in_at &&
            $this->clock_out_at &&
            $this->clock_in_at >= $this->clock_out_at
        ) {
            $validator->errors()->add(
                'clock_in_at',
                '出勤時間もしくは退勤時間が不適切な値です'
            );
        }

        //休憩チェック
        foreach ($this->breaks ?? [] as $index => $break){
            //休憩開始>退勤
            if (
                !empty($break['start']) &&
                $this->clock_out_at &&
                $break['start'] >= $this-> clock_out_at
            ) {
                $validator->errors()->add(
                    "breaks.$index.start",
                    '休憩時間が不適切な値です'
                );
            }
            //休憩終了　＞退勤
            if (
                !empty($break['end']) &&
                $this->clock_out_at &&
                $break['end'] > $this->clock_out_at
            ) {
                $validator->errors()->add(
                    "breaks.$index.end",
                    '休憩時間が不適切な値です'
                );
            }
        }
        });
    }
}