<?php

date_default_timezone_set('Europe/Athens');

function getBayesAvg($v, $v_avg, $a, $s) {
  return ($v / ($v + $v_avg)) * $a + ($v_avg / ($v + $v_avg)) * $s;
};

function getScores($db, $table) {
  if ($table === 'critic_votes') {
    $query = 'SELECT video, COUNT(*) AS votes, ROUND(AVG(rate), 1) AS video_avg FROM critic_votes GROUP BY video';
  } else {
    $query = 'SELECT video, COUNT(*) AS votes, ROUND(AVG(rate), 1) AS video_avg FROM votes GROUP BY video';
  }

  $videos = $db->query($query);
  $total_votes = $db->table($table)->count();

  if ($total_votes === 0) return;
  
  $video_count = $videos->count();
  $avg_votes_all = $total_votes / $video_count;
  $avg_rate_all = round(($db->table($table)->sum('rate') / $total_votes), 2);

  $results = [];

  foreach ($videos as $video) {
    $video->score = getBayesAvg($video->votes(), $avg_votes_all, $video->video_avg(), $avg_rate_all);
    array_push($results, $video);
  }

  return $results;
};

function set_cookie($user_type) {
  $cookie_name;

  if ($user_type === 'critic') {
    $cookie_name = 'critic_day';
  } else {
    $cookie_name = 'day';
  }

  Cookie::set($cookie_name . date('Y') . '_' . getDays()['current_day']->num(), 'true', [
    'lifetime' => strtotime(getDays()['current_day']->ends()),
    'httpOnly' => false
  ]);
};

function findProperty($array, $prop, $id) {
  if (!$array) return;

  foreach ($array as $item) {
    if ($item->video === $id) {
      return $item->$prop;
    }
  }

  return 0;
}

function showResults($all = false) {
  $db = Db::connect();

  $all_videos = kirby()->site()->pages()->get('videos')->children();
  $public_scores = getScores($db, 'votes');
  $critic_scores = getScores($db, 'critic_votes');

  $results = [];
  $num_votes = 0;

  for ($i = 0; $i < count($all_videos); $i++) {
    $id = $all_videos->nth($i)->video_id()->value();

    $result = [
      'id' => $id,
      'video' => $all_videos->nth($i)->title(),
      'votes' => findProperty($public_scores, 'votes', $id) + findProperty($critic_scores, 'votes', $id),
      'score' => round(
        (findProperty($public_scores, 'score', $id) * site()->user_weight()->toFloat()) +
        (findProperty($critic_scores, 'score', $id) * site()->critic_weight()->toFloat()), 2)
    ];

    array_push($results, (object) $result);
    $num_votes = $num_votes + $result['votes'];
  }

  usort($results, function($a, $b) { return $a->score < $b->score; });

  if ($all) {
    $final_results = $results;
  } else {
    $final_results = array_slice($results, 0, 3);
  }

  return [
    'results' => $final_results,
    'votes' => $num_votes
  ];
}

return [
  // @home: alex, alexrgb77
  'db' => [
    'host'     => '127.0.0.1',
    'database' => 'cosmolights',
    'user'     => 'root',
    'password' => 'design',
  ],
  'routes' => [
    // Initialize Db
    [
      'pattern' => 'initdb',
      'action' => function() {
        $db = Db::connect();

        $votes = 'CREATE TABLE votes(id INT, video VARCHAR(10), rate VARCHAR(10), PRIMARY KEY (id))';
        $critic_votes = 'CREATE TABLE critic_votes(id INT, video VARCHAR(10), rate VARCHAR(10), PRIMARY KEY (id))';

        if ($db->validateTable('votes')) {
          $db->dropTable('votes');
          $db->query($votes);
        } else {
          $db->query($votes);
        }
        
        if ($db->validateTable('critic_votes')) {
          $db->dropTable('critic_votes');
          $db->query($critic_votes);
        } else {
          $db->query($critic_votes);
        }
                
        return 'database initialized';
      }
    ],
    
    // Vote
    [
      'pattern' => 'vote',
      'action'  => function() {
        $payload = get();
        $user = kirby()->user($payload['user']);

        if ($user && ($user->role()->name() === 'critic')) {
          $db = Db::table('critic_votes');
          set_cookie('critic');
        } else {
          $db = Db::table('votes');
          set_cookie('user');
        }

        foreach ($payload['votes'] as $vote) {
          $db->insert([
            'id' => $db->max('id') + 1,
            'video' => $vote['video'],
            'rate' => $vote['rate']
          ]);
        }
        
        if ($user && ($user->role()->name() === 'critic')) { $user->logout(); };

        $tommorow = getDays()['next_day'];
        $results = getDays()['results_time'];
        $results_day = $results->toDate('%A %e %B');
        $results_time = $results->toDate('%R');

        $messages = [
          'success' => 'Η ψήφος σου καταχωρήθηκε.',
          'success_en' => 'Your vote has been registered.',
          'followup' => 'Μην ξεχάσεις να ψηφίζεις κάθε μέρα του διαγωνισμού!',
          'followup_en' => 'Don\'t forget to vote each day of the contest!',
          'results' => "Δες τα αποτελέσματα του διαγωνισμού την Κυριακή 30-08, μετά τις {$results_time}!",
          'results_en' => "Watch the contest results on Sunday 30-08, after {$results_time} (UTC/GMT +3 hours)!",
        ];

        return [
          'message' => $messages['success'] . ' ' . ($tommorow ? $messages['followup'] : $messages['results']),
          'message_en' => $messages['success_en'] . ' ' . ( $tommorow ? $messages['followup_en'] : $messages['results_en']),
        ];
      },
      'method' => 'POST'
    ],
  ],
  'languages' => true,
  'date.handler' => 'strftime',
  'schnti.cachebuster.active' => false,
  'debug' => true
];
