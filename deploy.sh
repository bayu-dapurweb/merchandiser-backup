git checkout public/assets/css/main.css
git checkout public/assets/css/main.css.map
git fetch origin
git merge origin/master --commit --no-edit
git merge origin/frontend --commit --no-edit
sass resources/assets/sass/main.scss public/assets/css/main.css --style compressed