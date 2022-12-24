Generate geany tags file for language javascript.
I use it tool from 2014 to 2019 year  with geany 1.23.1.
If you use old PC with geany 1.23.1, you can use it tool.
Latest geany version have javascript autocomplete support "from box".

For old geany version (1.23.1).

1 Install php 5.4 or more;
2 Download and extract geanyjsparser. Let path for geanyjsparser.php will /home/user/tools/geanyjsparser.php
3 Run geany and create new project
4 Create first *.js file for our project. Save. Restart geany.
4 Select menu Build->Setup build command
5 In first input line in group "Commands for language javascript" add line
php /home/user/tools/geanyjsparser.php %d/%f %p
6 Push OK button
7 Now, if you press F8 key, parser create file
/home/user/.config/geany/tags/geanyjsparser.js.tags
and restart geany. You need save all project files before press F8 key.

I use it tool from 2014 to 2019 year.
If you use old PC with geany 1.23.1, you can use it tool.
Enjoy, if you need.

