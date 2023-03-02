git fetch --all
git pull --all
git fetch
git branch -r #list branches

git fetch -p #The -p flag means "prune". After fetching, branches which no longer exist on the remote will be deleted.
git branch -d disabled # delete branch locally
git branch -d disabled # delete branch remotely
git push origin --delete disabled