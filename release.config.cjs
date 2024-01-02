/**
 * @type {import('semantic-release').GlobalConfig}
 */
module.exports = {
  branch: 'main',
  plugins: [
    ['@semantic-release/commit-analyzer', {'preset': 'conventionalcommits'}],
    '@semantic-release/release-notes-generator',
    '@semantic-release/github',
    '@semantic-release/git',
  ]
};
