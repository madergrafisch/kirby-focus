<?php

class Focus {

  /**
   * Calculates the image ratio by dividing width / height
   */
  public static function ratio($width, $height) {
    if ($height === 0) {
      return 0;
    }
    return $width / $height;
  }

  /**
   * Correct format, even for localized floats
   */
  public static function numberFormat($number) {
    return number_format($number,2,'.','');
  }


  public static function mapToRange($value, $in_min, $in_max, $out_min, $out_max ) {
    return ($value - $in_min) * ($out_max - $out_min) / ($in_max - $in_min) + $out_min;
  }


  /**
   * Calculates crop coordinates and width/height to crop and resize the original image
   */
  public static function cropValues($thumb) {
    // get original image dimensions
    $dimensions = clone $thumb->source->dimensions();
    $zoomfactor = static::mapToRange($thumb->options['zoom'], 0, 100, 1, 0.1);

    // calculate new height for original image based crop ratio
    if ($thumb->options['fit'] == 'width') {
      $width  = $dimensions->width();
      $height = floor($dimensions->width() / $thumb->options['ratio']);
    }

    // calculate new width for original image based crop ratio
    if ($thumb->options['fit'] == 'height') {
      $width  = $dimensions->height() * $thumb->options['ratio'];
      $height = $dimensions->height();
    }

    $width *=  $zoomfactor;
    $height *=  $zoomfactor;

    $widthHalf = floor($width / 2);
    $heightHalf = floor($height / 2);

    // calculate focus for original image
    $focusX = floor($dimensions->width() * $thumb->options['focusX']);
    $focusY = floor($dimensions->height() * $thumb->options['focusY']);

    $x1 = $focusX - $widthHalf;
    $y1 = $focusY - $heightHalf;

    // $y1 off canvas?
    $y1 = ($y1 < 0) ? 0 : $y1;
    $y1 = ($y1 + $height > $dimensions->height()) ? $dimensions->height() - $height : $y1;

    // $x1 off canvas?
    $x1 = ($x1 < 0) ? 0 : $x1;
    $x1 = ($x1 + $width > $dimensions->width()) ? $dimensions->width() - $width : $x1;

    $x2 = floor($x1 + $width);
    $y2 = floor($y1 + $height);

    return array(
      'x1' => $x1,
      'x2' => $x2,
      'y1' => $y1,
      'y2' => $y2,
      'width' => floor($width),
      'height' => floor($height),
    );
  }


  /**
   * Get the stored zoom value
   */
  public static function zoom($file) {
    $zoom = 0;

    $zoomFieldKey = c::get('focus.zoom.field.key', 'zoom');

    if ($file->$zoomFieldKey()->isNotEmpty()) {
      $zoom = $file->$zoomFieldKey()->value();
    }

    return $zoom;
  }


  /**
   * Get the stored coordinates
   */
  public static function coordinates($file, $axis = null) {
    $focusCoordinates = array(
      'x' => focus::numberFormat(0.5),
      'y' => focus::numberFormat(0.5),
    );

    $focusFieldKey = c::get('focus.field.key', 'focus');

    if ($file->$focusFieldKey()->isNotEmpty()) {
      $focus = json_decode($file->$focusFieldKey()->value());
      $focusCoordinates = array(
        'x' => focus::numberFormat($focus->x),
        'y' => focus::numberFormat($focus->y),
      );
    }

    if (isset($axis) && isset($focusCoordinates[$axis])) {
      return $focusCoordinates[$axis];
    }

    return $focusCoordinates;
  }

}

