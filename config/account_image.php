<?php

return [
    // Crop top pixels from account images (main + gallery).
    'top_crop_px' => (int) env('ACCOUNT_IMAGE_TOP_CROP_PX', 35),

    // Blur account name area on main image only.
    'name_blur' => [
        'enabled' => (bool) env('ACCOUNT_IMAGE_NAME_BLUR_ENABLED', true),
        // Position mode: fixed (px) or adaptive (relative to image dimensions).
        'mode' => env('ACCOUNT_IMAGE_NAME_BLUR_MODE', 'adaptive'),
        'x_offset_from_right' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_X_OFFSET_FROM_RIGHT', 420),
        'y' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_Y', 40),
        'width' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_WIDTH', 350),
        'height' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_HEIGHT', 100),
        // Adaptive ratios (used when mode=adaptive)
        'x_offset_from_right_ratio' => (float) env('ACCOUNT_IMAGE_NAME_BLUR_X_OFFSET_FROM_RIGHT_RATIO', 0.39),
        'y_ratio' => (float) env('ACCOUNT_IMAGE_NAME_BLUR_Y_RATIO', 0.037),
        'width_ratio' => (float) env('ACCOUNT_IMAGE_NAME_BLUR_WIDTH_RATIO', 0.325),
        'height_ratio' => (float) env('ACCOUNT_IMAGE_NAME_BLUR_HEIGHT_RATIO', 0.093),
        'strength' => (int) env('ACCOUNT_IMAGE_NAME_BLUR_STRENGTH', 35),
    ],
];

