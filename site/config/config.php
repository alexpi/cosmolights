<?php

date_default_timezone_set('Europe/Athens');

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
      'pattern' => 'toomaninitdb',
      'action' => function() {
        $db = Db::connect();

        $publicTable = 'CREATE TABLE public(id INT, sub VARCHAR(320), votes JSON, day1 BOOLEAN, day2 BOOLEAN, day3 BOOLEAN, PRIMARY KEY (id))';
        $criticsTable = 'CREATE TABLE critics(id INT, sub VARCHAR(320), votes JSON, day1 BOOLEAN, day2 BOOLEAN, day3 BOOLEAN, PRIMARY KEY (id))';

        if ($db->validateTable('public')) {
          $db->dropTable('public');
          $db->query($publicTable);
        } else {
          $db->query($publicTable);
        }
        
        if ($db->validateTable('critics')) {
          $db->dropTable('critics');
          $db->query($criticsTable);
        } else {
          $db->query($criticsTable);
        }
                
        return 'database initialized';
      }
    ],

    // Auth
    [
      'pattern' => 'auth',
      'action' => function() {
        $auth = [
          'domain' => 'dev-3yac250y.eu.auth0.com',
          'clientId' => 'tTivUssGOzzSdw9ySpqGsaeBfHP3VN8o'
        ];

        return json_encode($auth);
      },
      'method' => 'POST'
    ],
    
    // Vote
    [
      'pattern' => 'vote',
      'action'  => function() {
        $data = get()['vote'];
        $current_day_index = getDays()['current_day']->indexOf() + 1;
        $tommorow = getDays()['next_day'];
        $results = getDays()['results_time'];
        $results_day = $results->toDate('%A %e %B');
        $results_time = $results->toDate('%R');

        $messages = [
          'already_voted' => 'Έχεις ήδη ψηφίσει για σήμερα.',
          'already_voted_en' => 'You already voted for today.',
          'success' => 'Η ψήφος σου καταχωρήθηκε.',
          'success_en' => 'Your vote has been registered.',
          'followup' => 'Μην ξεχάσεις να ψηφίζεις κάθε μέρα του διαγωνισμού!',
          'followup_en' => 'Don\'t forget to vote each day of the contest!',
          'results' => "Δες τα αποτελέσματα του διαγωνισμού την Κυριακή 30-08, μετά τις {$results_time}!",
          'results_en' => "Watch the contest results on Sunday 30-08, after {$results_time} (UTC/GMT +3 hours)!",
        ];

        if ($data['type'] === 'critic') {
          $db = Db::table('critics');
        } else {
          $db = Db::table('public');
        }

        $voter = $db->findBy('sub', $data['sub']);
        $day_key = 'day' . $current_day_index;

        if (empty($voter)) {
          $db->insert([
            'id' => $db->max('id') + 1,
            'sub' => $data['sub'],
            'votes' => json_encode($data['votes']),
            $day_key => true
          ]);
        } else {
          if ($voter->{$day_key}) {
            return [
              'error' => $messages['already_voted'],
              'error_en' => $messages['already_voted_en']
            ];
          } else {
            $db->update([
              'votes' => array_merge(json_decode($voter->votes(), true), $data['votes']),
              $day_key => true
            ],[
              'id' => $voter->id()
            ]);
          }
        }

        return [
          'data' => $data,
          'message' => $messages['success'] . ' ' . ($tommorow ? $messages['followup'] : $messages['results']),
          'message_en' => $messages['success_en'] . ' ' . ($tommorow ? $messages['followup_en'] : $messages['results_en']),
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
