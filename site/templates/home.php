<!DOCTYPE html>
<html lang="<?= $kirby->language()->code() ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $page->title() ?></title>

  <!-- Primary Meta Tags -->
  <meta name="title" content="<?= $page->meta_title() ?>">
  <meta name="description" content="<?= $page->meta_description() ?>">

  <!-- Open Graph / Facebook -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= $page->meta_url() ?>">
  <meta property="og:title" content="<?= $page->meta_title() ?>">
  <meta property="og:description" content="<?= $page->meta_description() ?>">
  <meta property="og:image" content="<?= $page->meta_image()->toFile()->url() ?>">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?= $page->meta_url() ?>">
  <meta property="twitter:title" content="<?= $page->meta_title() ?>">
  <meta property="twitter:description" content="<?= $page->meta_description() ?>">
  <meta property="twitter:image" content="<?= $page->meta_image()->toFile()->url() ?>">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700;900&display=swap" rel="stylesheet">
  <?= css(['assets/reset.css', 'assets/styles.css']) ?>
</head>
<body data-in-progress="<?= isset($day) ? 'true' : 'false' ?>">
  <?php if ($user = $kirby->user()): ?>
    <h4 class="user-type">Έχετε συνδεθεί <strong><?= $user->name() ?></strong></h4>
  <?php endif ?>

  <header class="header">
    <nav>
      <?php foreach($kirby->languages() as $language): ?>
      <div <?php e($kirby->language() == $language, ' class="selected"') ?>>
        <a href="<?= $language->url() ?>" hreflang="<?php echo $language->code() ?>">
          <?= html($language->code()) ?>
        </a>
      </div>
      <?php endforeach ?>
    </nav>

    <div class="title">
      <h1><?= $site->title() ?></h1>
    </div>

    <?php if (isset($day)): ?>
      <div class="day">
        <h2><span class="number"><?= isset($day) ? addOrdinalNumberSuffix($day->num()) : '' ?></span><span class="word"><?= t('day') ?></span></h2>
      </div>
    <?php endif ?>
  </header>

  <main>
    <div class="intro">
      <h1 class="event-title">Cosmolights</h1>
      <img class="lockup" src="<?= url('assets/img/lockup.svg') ?>" alt="1st international projection mapping competition in Greece">
      
      <?php if (isset($day)): ?>
        <div class="info">
          <div class="event">
            <div class="icon">
              <svg width="45" height="45" viewBox="0 0 45 45" fill="none">
                <path d="M30 22.3214L18.2143 13.5714V31.0714L30 22.3214Z" fill="white"/>
                <circle cx="22.5" cy="22.5" r="20" stroke="white" stroke-width="5" />
              </svg>
            </div>

            <p><?php echo tt('view-on',
              ['facebook' => $site->fb_link(),
               'youtube' => $site->yt_link()
              ]) ?>
            </p>
          </div>

          <p>
            <?php if (!$kirby->user()): ?>
              <span data-login-instruction><?= t('login-instruction') ?></span>
            <?php endif ?>

            <?= $site->instructions() ?>
          </p>

          <?php if (!$kirby->user()): ?>
            <button class="login" data-login><?= t('login') ?></button>
          <?php endif ?>
        </div>

        <div class="day mobile">
          <h2><span class="number"><?= isset($day) ? addOrdinalNumberSuffix($day->num()) : '' ?></span><span class="word"><?= t('day') ?></span></h2>
        </div>
      <?php endif ?>
    </div>

    <?php if (!isset($day) && $next_day): ?>
      <div class="day-info">
        <p><?= tt('voting-closed', [
          'vote-start' => $next_day->starts()->toDate('%R'),
          'vote-end' => $next_day->ends()->toDate('%R'),
          'day' => $next_day->title()->lower()
          ])?>
        </p>
      </div>
    <?php endif ?>

    <?php if ($voting_ended && !$results): ?>
      <div class="day-info">
        <p><?= tt('voting-ended', [
          'day' => $results_datetime->toDate('%A'),
          'time' => $results_datetime->toDate('%R'),
          ])?>
        </p>
        <!-- <p><?= $site->vote_end() ?></p> --> <!-- use for manual ending message -->
      </div>
    <?php endif ?>

    <?php if (isset($day)): ?>
      <div class="videos-container">
        <h2 class="heading">Videos</h2>

        <form class="videos-form">
          <?php if ($critic = $kirby->user()): ?>
            <input type="hidden" name="critic" data-element="critic" value="<?= $critic->id() ?>">
          <?php endif ?>

          <div class="videos">
            <?php if ($videos): ?>
              <?php foreach($videos as $video): ?>
                <div class="video" data-id="<?= $video->content()->video_id() ?>">
                  <div class="video-container">
                    <video nocontrols>
                      <source src="<?= $video->videos()->first()->url() ?>">
                    </video>

                    <svg class="play" viewBox="0 0 19 25">
                      <path d="M19 12.5L0 0V25L19 12.5Z" />
                    </svg>
                  </div>

                  <div class="main">
                    <h2 class="title">
                      <span class="artist"><?= $video->title() ?></span>
                      <span class="country"><?= $video->country() ?></span></h2>

                    <div class="group">
                      <div class="rating">
                        <h3 class="<?= e($kirby->user(), '', 'inactive') ?>" data-element="rate-title"><?php echo t('your-rating') ?>:</h3>

                        <div class="rates">
                          <?php for ($i = 1; $i < 6; $i++): ?>
                            <button class="rate" type="button" value="<?= $i ?>" data-element="rate" <?= e($kirby->user(), '', 'disabled') ?>><?= $i ?></button>
                          <?php endfor ?>
                        </div>
                      </div>
                    </div>                 
                  </div>
                </div>
              <?php endforeach ?>
            <?php endif ?>
          </div>

          <div class="submit-container">
            <button type="button" id="submit">
              <span><?php echo t('send-your-vote') ?></span>

              <div class="icon">
                <svg class="send" viewBox="0 0 26 26" fill="none">
                  <path d="M15.3333 10.7676L10.8333 15.2941L4 11.1588L23 3L15.0556 22L12.7778 18.4235" stroke="white" stroke-width="3" />
                </svg>

                <svg class="check" width="34" height="25" viewBox="0 0 34 25">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M12.0284 18.1035L30.4845 0L34 3.44828L12.0284 25L0 13.2015L3.51546 9.7532L12.0284 18.1035Z" fill="white"/>
                </svg>
              </div>
            </button>

            <p class="message"></p>
          </div>
        </form>
      </div>
    <?php endif ?>

    <?php if ($results): ?>
      <div class="results">
        <h2 class="heading"><?= t('winners') ?></h2>

        <table class="results-table">
          <tr>
            <th align="left"><?= t('position') ?></th>
            <th align="left">Video</th>
            <th align="left"><?= t('score') ?></th>
          </tr>
          <?php foreach ($results as $key => $result): ?>
            <tr class="result" border="1">
              <td class="position"><?= $key + 1 ?></td>
              <td><?= $result->video ?></td>
              <td><?= $result->score ?></td>
            </tr>
          <?php endforeach ?>
        </table>
      </div>
    <?php endif ?>
  </main>

  <footer>
    <p id="privacy" class="privacy"><?= t('privacy') ?></p>
    <p class="copyright"><a href="https://www.cosmopolisfestival.gr" target="_blank">cosmopolisfestival.gr</a> © 2021</p>
  </footer>

  <script src="https://cdn.auth0.com/js/auth0-spa-js/1.13/auth0-spa-js.production.js"></script>
  <?= js('assets/app.js') ?>
</body>
</html>