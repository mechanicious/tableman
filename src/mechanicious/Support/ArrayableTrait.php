<?php namespace mechanicious\Support;

trait ArrayableTrait
{
  /**
   * Returns what $has has, and $leaks leaks.
   * @param  array $has
   * @param  array $leaks
   * @see http://stackoverflow.com/questions/3876435/recursive-array-diff
   * @return array
   */
  public function array_diff_recursive($has, $leaks) {
    $aReturn = array();

    foreach ($has as $mKey => $mValue) {
      if (array_key_exists($mKey, $leaks)) {
        if (is_array($mValue)) {
          $aRecursiveDiff = $this->array_diff_recursive($mValue, $leaks[$mKey]);
          if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
        } else {
          if ($mValue != $leaks[$mKey]) {
            $aReturn[$mKey] = $mValue;
          }
        }
      } else {
        $aReturn[$mKey] = $mValue;
      }
    }
    return $aReturn;
  }
}