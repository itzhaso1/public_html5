<?php

return [
    // Crop top pixels from account images (main + gallery).
    'top_crop_px' => (int) env('ACCOUNT_IMAGE_TOP_CROP_PX', 35),

    // Blur account name area on main image only.
    'name_blur' => [
        'enabled' => (bool) env('ACCOUNT_IMAGE_NAME_BLUR_ENABLED', true),
        'x_offset_from_right' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_X_OFFSET_FROM_RIGHT', 420),
        'y' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_Y', 40),
        'width' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_WIDTH', 350),
        'height' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_HEIGHT', 100),
        'strength' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_STRENGTH', 35),
    ],
];

