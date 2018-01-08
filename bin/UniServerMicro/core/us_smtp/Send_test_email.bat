@ECHO OFF
rem #############################################################
rem # Name: Send_test_email.bat
rem # Created By: The Uniform Server Development Team
rem # Edited Last By: Mike Gleaves (ric)
rem # V 1.0 20-11-2011
rem # Send email from command line via SMTP using MSMTP
rem # The ECHO. line after ECHO Subject: is important and must be included
rem ##############################################################


rem ### working directory current folder 
pushd %~dp0

ECHO Subject: This is a test   >> %TEMP%\temp.mail
ECHO.                          >> %TEMP%\temp.mail
ECHO Testing (your content).   >> %TEMP%\temp.mail
ECHO More content blah blah..  >> %TEMP%\temp.mail
ECHO.                          >> %TEMP%\temp.mail
ECHO blah.                     >> %TEMP%\temp.mail

us_smtp -f %cd%\us_smtp.ini -t < %TEMP%\temp.mail

DEL %TEMP%\temp.mail

pause

rem ### restore original working directory
popd
