<?php

return function() {
  $current_day = getDays()['current_day'];
  $next_day = getDays()['next_day'];
  $end_datetime = getDays()['end_datetime'];
  $results_datetime = getDays()['results_time'];

  $videos = null;
  $results = null;
  $voting_ended = false;

  if (isset($current_day)) {
    $videos = $current_day->content()->videos()->toPages();
  }

  if (time() > $end_datetime->toDate()) {
    $voting_ended = true;
  }

  if (time() > $results_datetime->toDate()) {
    $results = calculateResults();
  }

  return [
    'day' => $current_day,
    'next_day' => $next_day,
    'voting_ended' => $voting_ended,
    'videos' => $videos,
    'results_datetime' => $results_datetime,
    'results' => $results ? $results['results'] : null
  ];
};