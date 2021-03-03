<?php

return function() {
  $results = showResults(true);
  $video_count = kirby()->site()->pages()->get('videos')->children()->count();
  $voted = $results['votes'] / $video_count;
  
  return [
    'results' => $results['results'],
    'voted' => $voted,
  ];
};