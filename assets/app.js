import { getVotes, vote, checkCookie, showMessage } from './helpers.js';

const lang = document.documentElement.lang;
const day = document.body.dataset.day;
const videos = document.querySelector('.videos');

const playVideos = videos => {
  if (!videos) return;

  const play = video => {
    video.play();
    const playIcon = video.parentElement.querySelector('.play');
    playIcon.classList.add('playing');
  }

  const pause = video => {
    video.pause();
    const playIcon = video.parentElement.querySelector('.play');
    playIcon.classList.remove('playing');
  }

  let previousVideo;

  Array.from(videos.children).forEach(video => {
    const videoEl = video.querySelector('video');
    let isPlaying = false;

    videoEl.addEventListener('click', event => {
      const currentVideo = event.target;

      if (previousVideo) {
        pause(previousVideo);

        if (previousVideo !== currentVideo) {
          isPlaying = false;
        }
      }

      previousVideo = currentVideo;

      if (isPlaying === false) {
        play(currentVideo);
        isPlaying = true;
      } else {
        pause(currentVideo);
        isPlaying = false;
      }
    });
  })
}

const handleRates = videos => {
  if (!videos) return;

  Array.from(videos.children).forEach(video => {
    const rates = video.querySelector('.rates');
    const buttonGroupArray = Array.from(rates.children);

    video.addEventListener('click', event => {
      buttonGroupArray.forEach(button => {
        if ((button !== event.target) && (event.target.nodeName === 'BUTTON')) {
          button.classList.remove('selected');
        }
      });

      if (event.target.nodeName === 'BUTTON') {
        event.target.classList.toggle('selected');
      }
    });
  });
}

const submitVotes = videos => {
  const user = document.querySelector('.user');
  const submit = document.querySelector('#submit');
  const message = document.querySelector('.message');

  if (!submit) return;

  submit.addEventListener('click', event => {
    event.preventDefault();

    const votes = getVotes(videos);

    if (votes.length === 0) {
      if (lang === 'el') {
        message.textContent = 'Δεν έχεις επιλέξει video';
      } else {
        message.textContent = 'You haven\'t selected any videos';
      }

      showMessage(message);
      return;
    }

    if (checkCookie(user, day)) {
      if (lang === 'el') {
       message.textContent = 'Έχεις ήδη ψηφίσει για τη σημερινή ημέρα';
      } else {
        message.textContent = 'You have already voted for today';
      }

      showMessage(message);
      return;
    }

    submit.classList.add('voted');

    vote(user ? user.value : 'public', votes)
      .then(res => {
        if (lang === 'el') {
          message.textContent = res.message;
        } else {
          message.textContent = res.message_en;
        }

        showMessage(message);
      })
  });
};

playVideos(videos);
handleRates(videos);
submitVotes(videos);
