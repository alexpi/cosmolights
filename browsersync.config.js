const config = {
  open: false,
  proxy: 'localhost/cosmolights/',
  // online: true,
  // tunnel: 'cosmolights',
  files: [
    'assets/**/*.*',
    "site/blueprints/**/*.yml",
    "site/controllers/*.php",
    "site/templates/*.php",
    "site/snippets/**/*.php",
    'content/**/*.txt'
  ]
}

module.exports = config;
