#delete branch
git fetch -p #The -p flag means "prune". After fetching, branches which no longer exist on the remote will be deleted.
git branch -d localBranchName # delete branch locally
git push origin --delete remoteBranchName # delete branch remotely

git fetch --all
git pull --all

# pulling branch without switching
git fetch --all ; 
git fetch origin side:side
git fetch origin third:third



