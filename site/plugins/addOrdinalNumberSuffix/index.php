<?php

function addOrdinalNumberSuffix($num) {
  if (kirby()->language() == 'en') {
    if (!in_array(($num % 100),array(11,12,13))){
      switch ($num % 10) {
        case 1: return $num . 'st';
        case 2: return $num . 'nd';
        case 3: return $num . 'rd';
      }
    }
    return $num . 'th';
  } else {
    return $num . 'η';
  }
}