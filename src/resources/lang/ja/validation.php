<?php

return [

    'required' => ':attribute を入力してください',
    'email' => ':attribute はメールアドレス形式で入力してください',
    'min' => [
        'string' => ':attribute は:min文字以上で入力してください',
    ],
    'max' => [
        'string' => ':attribute は:max文字以内で入力してください',
    ],
    'confirmed' => ':attribute が一致しません',
    'unique' => 'この:attributeは既に登録されています',

    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
    ],
];