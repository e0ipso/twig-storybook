/**
 * @type {import('semantic-release').GlobalConfig}
 */
module.exports = {
  branches: [{name: 'main'}, {name: 'next'}],
  plugins: [
    ['@semantic-release/commit-analyzer', {'preset': 'conventionalcommits'}],
    '@semantic-release/release-notes-generator',
    '@semantic-release/github',
    '@semantic-release/git',
  ]
};
