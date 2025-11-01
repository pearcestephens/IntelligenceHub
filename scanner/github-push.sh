#!/bin/bash
# Scanner deployment helper
# Scanner now lives inside the IntelligenceHub monorepo.

clear
cat <<'EOF'
============================================================
   Scanner Dashboard GitHub Push Helper (Updated Workflow)
============================================================

The Scanner application now lives inside the main repository:
  https://github.com/pearcestephens/IntelligenceHub

To push changes:
  1) Work from the project root:
       cd /home/master/applications/hdgwrzntwa/public_html

  2) Review changes:
       git status

  3) Commit:
       git add <files>
       git commit -m "Describe your change"

  4) Push to GitHub:
       git push origin master

The historical standalone scanner repository has been retired.
Use the IntelligenceHub monorepo for all future commits.
EOF
