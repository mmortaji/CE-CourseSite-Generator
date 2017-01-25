<h1>Features</h1>
<br>
<strong>CE CourseSite Generator</strong> is an open source package which is designed to have maximum flexibility.
<br>Some of its features are as follows:

  - It dosn't use any database and all the required data is saved on XML files.
  <br>(I used XML files for two reasons. first using database reduces execution speed. second working with databases requires having database account on the server and many users don't have one.)

  - It is very easy to create new themes for your website. Default theme is on themes/default directory. It contains a CSS file on themes/default/style directory and images used with that CSS file on themes/default/images. I will be glad if you create new themes and send them to me to put them on next versions.
      

<h1>Base Requirements</h1>
<br>
In order to use CE CourseSite Generator the following prequisites are necessary.

    Apache Web Server (You can also use other web servers but it will reduce security of your website).
    PHP version 4.3.0 Until 7.0.4 or better installed as an apache module.
    Linux/Unix Server


<h1>Installing the package</h1>
<br>
To create a website for your course do the followings:
<br>
    Create a directory for your course on the server.<br>
    Run this command on linux shell:<br>
        chmod 777 your-course-directory<br>
    Download install.php and CoursePackage.tar.gz and copy them to your-course-directory.<br>
    In your web browser go to http://course-website/install.php<br>
    Setup will install the package and will set configurations.<br>
    After the installation completed run this command on linux shell:<br>
       chmod 755 your-course-directory<br>

