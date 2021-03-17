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

function getBayesAvg($v, $v_avg, $a, $s) {
  return ($v / ($v + $v_avg)) * $a + ($v_avg / ($v + $v_avg)) * $s;
};

function rateSum($votes) {
  return array_reduce($votes, function($carry, $vote) {
    return $carry + $vote->rate;
  }, 0);
}

function getScores($table) {
  $videos = kirby()->site()->pages()->get('videos')->children();
  $votes_values = Db::select($table, ['votes'])->values();
  $votes = [];
  $scores = [];

  $votes_array = array_map(function($vote) {
    return json_decode($vote->votes());
  }, $votes_values);

  foreach ($votes_array as $vote) {
    foreach ($vote as $v) {
      array_push($votes, $v);
    }
  }

  $total_votes = count($votes);
  
  if ($total_votes === 0) return;
  
  $video_count = $videos->count();
  $avg_votes_all = $total_votes / $video_count;
  $rates_sum = rateSum($votes);

  $avg_rate_all = round(($rates_sum / $total_votes), 2);

  foreach ($videos as $video) {
    $id = $video->video_id()->value();

    $video_votes = array_filter($votes, function($vote) use ($id) {
      return $vote->video === $id;
    });

    $video_votes_count = count($video_votes);
    $video_rates_sum = rateSum($video_votes);
    $video_average = $video_votes_count ? $video_rates_sum / $video_votes_count : 0;

    array_push($scores, [
      'id' => $id,
      'votes' => $video_votes_count,
      'score' => getBayesAvg($video_votes_count, $avg_votes_all, $video_average, $avg_rate_all)
    ]);
  }

  return $scores;
};

function calculateResults($all = false) {
  $videos = kirby()->site()->pages()->get('videos')->children();
  $public_scores = getScores('public');
  $critic_scores = getScores('critics');

  $results = [];

  for ($i = 0; $i < count($videos); $i++) {
    $id = $videos->nth($i)->video_id()->value();

    $result = [
      'id' => $id,
      'video' => $videos->nth($i)->title(),
      'votes' => $public_scores[$i]['votes'] + $critic_scores[$i]['votes'],
      'score' => round(
        ($public_scores[$i]['score'] * site()->public_weight()->toFloat()) +
        ($critic_scores[$i]['score'] * site()->critic_weight()->toFloat()), 2)
    ];

    array_push($results, (object) $result);
  }

  usort($results, function($a, $b) { return $a->score < $b->score; });

  if ($all) {
    $final_results = $results;
  } else {
    $final_results = array_slice($results, 0, 3);
  }

  return [
    'results' => $final_results
  ];
}