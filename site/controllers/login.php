<?php

return function ($kirby) {
  $error = false;

  if ($kirby->request()->is('POST') && get('login')) {
    if ($user = $kirby->user(get('email'))) {
      try {
        $user->login(get('password'));
        go('/');
      } catch (Exception $e) {
        $error = true;
      }
    } else {
      $error = true;
    }
  }

  return [
    'error' => $error
  ];
};