git fetch --all
git pull --all
git pull

git fetch
git branch -r #list branches

#delete branch
git fetch -p #The -p flag means "prune". After fetching, branches which no longer exist on the remote will be deleted.
git branch -d localBranchName # delete branch locally
git push origin --delete remoteBranchName # delete branch remotely

git config --get remote.origin.fetch

for branch in `git branch -a | grep remotes | grep -v HEAD | grep -v master`; do
    git branch --track ${branch##*/} $branch
done

git fetch --all
git pull --all

git fetch origin
git reset --hard origin/main
git clean -f -d


git sync 
git sync 



