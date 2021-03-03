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

const vote = (user, votes) => {
  return fetch('vote', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ user, votes }),
  })
  .then(res => res.json())
  .catch(err => console.error(err))
}

const checkCookie = (user, name) => {
  user ? name = `critic_${name}` : name;

  if (document.cookie.split(';').some(item => item.trim().startsWith(`${name}=`))) {
    return true;
  }

  return false;
}

const showMessage = elem => elem.classList.add('show');

export {
  getVotes,
  vote,
  checkCookie,
  showMessage,
}
