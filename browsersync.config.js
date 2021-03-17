const config = {
  open: false,
  proxy: 'http://cosmolights.local',
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
