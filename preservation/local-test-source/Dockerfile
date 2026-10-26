FROM php:7.4-cli
RUN docker-php-ext-install mysqli pdo_mysql
RUN printf 'short_open_tag=On\ndisplay_errors=Off\nlog_errors=On\nerror_reporting=E_ALL & ~E_NOTICE & ~E_DEPRECATED\nmemory_limit=512M\nupload_max_filesize=128M\npost_max_size=128M\ndisable_functions=mail,exec,shell_exec,system,passthru,popen,proc_open\n' > /usr/local/etc/php/conf.d/local.ini
WORKDIR /app
