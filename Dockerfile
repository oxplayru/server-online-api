FROM tutum/apache-php
RUN apt-get update && apt-get install -yq git && rm -rf /var/lib/apt/lists/*
RUN rm -rf /app
RUN git clone https://github.com/oxplayru/server-online-api.git /app
ADD ./config.ini /app/config.ini
ENV ALLOW_OVERRIDE=true
RUN composer install

