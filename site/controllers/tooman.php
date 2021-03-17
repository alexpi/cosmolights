<?php

return function() {
  $results = calculateResults(true);
  $video_count = kirby()->site()->pages()->get('videos')->children()->count();
  $voted = Db::select('public', ['votes'])->count();
  
  return [
    'results' => $results['results'],
    'voted' => $voted,
  ];
};