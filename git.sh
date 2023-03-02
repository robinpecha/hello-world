git fetch --all
git pull --all

git fetch
git branch -r #list branches

#delete branch
git fetch -p #The -p flag means "prune". After fetching, branches which no longer exist on the remote will be deleted.
git branch -d localBranchName # delete branch locally
git push origin --delete remoteBranchName # delete branch remotely

git config --get remote.origin.fetch
