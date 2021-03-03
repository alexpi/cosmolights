const form = document.querySelector('form');

const login = formData => {
  fetch('http://localhost:1337/auth/local', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      identifier: formData.identifier,
      password: formData.password
    })
  })
  .then(res => res.json())
  .then(res => {
    if (res.user) {
      localStorage.setItem('token', res.jwt);
      window.location.replace('/');
    }
  })
  .catch(error => {
    console.log('An error occurred:', error);
  });
};

form.addEventListener('submit', event => {
  event.preventDefault();

  const formData = new FormData(form);
  let data = {};

  for (let [key, prop] of formData) {
    data[key] = prop;
  }

  data = { identifier: "Critic 1", password: "criticpass" } // remove

  login(data);
});
