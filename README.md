# id.kb.se keywords
Integrates controlled vocabularies from [id.kb.se](id.kb.se) with the keyword field in OJS3.

Currently, keywords from the subject scheme SAO (Svenska ämnesord, https://id.kb.se/term/sao) can be linked for new OJS articles.

![Screenshot](https://repository-images.githubusercontent.com/362409815/08b58e00-b34a-11eb-81a9-c2eef6558421)

Creating a new release
----------------------
Bump the plugin version in `idkbse/version.xml`.

In the root directory (idkbse-ojs-plugin), create a tar file with the latest code:
```
tar czf idkbse.tar.gz --directory=$(pwd) idkbse/
```
Create a new github-release, tag the new version (`v.<M>.<m>.<p>`) and attach `idkbse.tar.gz`. 
