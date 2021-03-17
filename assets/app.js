const videos = document.querySelector('.videos');
const rateTitles = document.querySelectorAll('[data-element="rate-title"');
const rateButtons = document.querySelectorAll('[data-element="rate"]');
const loginBtn = document.querySelector('[data-login]');
const loginInstruction = document.querySelector('[data-login-instruction');

const messages = {
  'notLogged': 'Συνδεθείτε για να ψηφίσετε!',
  'notLogged_en': 'Log in to vote!',
  'noVideo': 'Δεν έχεις επιλέξει video',
  'noVideo_en': 'You haven\'t selected any videos',
};

const lang = document.documentElement.lang === 'en' ? 'en' : '';
let auth0 = null;

const getVotes = element => {
  const votesArray = Array.from(element.children);

  return votesArray.reduce((result, item) => {
    const selectedButton = item.querySelector('.selected');

    if (selectedButton === null) return result;

    const rate = selectedButton.value;
    result.push({ video: item.dataset.id, rate });

    return result;
  }, []);
};

const sendVote = vote => {
  return fetch('vote', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ vote }),
  })
  .then(res => res.json())
  .catch(err => console.error(err))
}

const showMessage = (messageEl, messageEn) => {
  const elem = document.querySelector('.message');
  const lang = document.documentElement.lang;

  if (lang === 'el') {
    elem.textContent = messageEl;
  } else {
    elem.textContent = messageEn;
  }

  elem.classList.add('show');
}

const fetchAuthConfig = () => fetch('auth', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  }
});

const configureClient = async () => {
  const response = await fetchAuthConfig();
  const config = await response.json();

  auth0 = await createAuth0Client({
    domain: config.domain,
    client_id: config.clientId
  });
};

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
  const critic = document.querySelector('[data-element="critic"]');
  const submit = document.querySelector('#submit');

  if (!submit) return;

  submit.addEventListener('click', async event => {
    event.preventDefault();

    const votes = getVotes(videos);
    let isAuthenticated = null;
    let userInfo = null;

    if (!critic) {
      isAuthenticated = await auth0.isAuthenticated();
      userInfo = await auth0.getUser();
    }

    if (!critic && !isAuthenticated) {
      showMessage(messages.notLogged, messages.notLogged_en);
      return;
    }

    if (votes.length === 0) {
      showMessage(messages.noVideo, messages.noVideo_en);
      return;
    }

    const vote = {
      type: critic ? 'critic' : 'public',
      sub: critic ? critic.value : userInfo.sub,
      votes
    };

    sendVote(vote)
      .then(res => {
        if (res.message || res.message_en) {
          showMessage(res.message, res.message_en);
          submit.classList.add('voted');
        } else {
          showMessage(res.error, res.error_en);
        }
      })
  });
};

const login = async (lang) => {
  await auth0.loginWithRedirect({
    redirect_uri: `${window.location.origin}/${lang}`
  });
};

const updateUI = async () => {
  const isAuthenticated = await auth0.isAuthenticated();

  if (isAuthenticated) {
    loginBtn.classList.add('hidden');
    loginInstruction.classList.add('hidden');

    [...rateTitles].forEach(title => title.classList.remove('inactive'));
    [...rateButtons].forEach(rate => rate.removeAttribute('disabled'));
  }
};

window.onload = async () => {
  const inProgress = document.body.dataset.inProgress;
  const critic = document.querySelector('[data-element="critic"]');

  if (inProgress === 'false' || critic) return;

  await configureClient();
  updateUI();
  const query = window.location.search;

  if (query.includes("code=") && query.includes("state=")) {
    await auth0.handleRedirectCallback();
    updateUI();
    window.history.replaceState({}, document.title, `/${lang}`);
  }
}

if (loginBtn) {
  loginBtn.addEventListener('click', () => login(lang));
}

playVideos(videos);
handleRates(videos);
submitVotes(videos);
