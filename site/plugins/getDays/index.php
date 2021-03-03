<?php

function getDays() {
  $now = time();
  $days = page('days')->children();
  $current_day = null;
  $next_day = null;
  $end_datetime = $days->last()->ends();
  $results_datetime = kirby()->site()->results_time();

  for ($i = 0; $i < $days->count(); $i++) {
    $starts = $days->nth($i)->starts()->toDate();
    $ends = $days->nth($i)->ends()->toDate();

    if ($now > $starts && $now < $ends) {
      $current_day = $days->nth($i);
    }

    if ($now < $starts) {
      $next_day = $days->nth($i);
      break;
    }
  }

  return [
    'current_day' => $current_day,
    'next_day' => $next_day,
    'end_datetime' => $end_datetime,
    'results_time' => $results_datetime,
  ];
}