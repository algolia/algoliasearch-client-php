export default {
  header: `## Summary`,

  versionChangeHeader: `## Version Changes`,
  skippedCommitsHeader: `### Skipped Commits`,
  skippedCommitsDesc: `It doesn't mean these commits are being excluded from the release. It means they're not taken into account when the release process figured out the next version number, and updated the changelog.`,
  noCommit: `no commit`,
  currentVersionNotFound: `current version not found`,
  descriptionVersionChanges: [
    `**Checked** → Update version, update repository, and release the library.`,
    `**Un-checked** → Do nothing`,
  ].join('\n'),
  indenpendentVersioning: `
  <details>
    <summary>
      <i>The JavaScript repository consists of several packages with independent versioning. Release type is applied to each version.</i>
    </summary>

    For example, if the release type is \`patch\`,

    * algoliasearch@5.0.0 -> 5.0.1
    * @algolia/client-search@5.0.0 -> 5.0.1
    * @algolia/client-abtesting@5.0.0 -> 5.0.1
    * ...
    * @algolia/client-predict@0.0.1 -> 0.0.2
    * ...
    * @algolia/requester-browser-xhr@0.0.5 -> 0.0.6.
  </details>
  `,
  descriptionForSkippedLang: `  - No \`feat\` or \`fix\` commit, thus unchecked by default.`,

  changelogHeader: `## CHANGELOG`,
  changelogDescription: `Update the following lines. Once merged, it will be reflected to \`changelogs/*.\``,

  approvalHeader: `## Approval`,
  approval: [
    `To proceed this release, a team member must leave a comment "approved" in this issue.`,
    `To skip this release, just close it.`,
  ].join('\n'),
};
