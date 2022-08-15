# Build stage

FROM node:18-bullseye AS builder

WORKDIR /tmp/

COPY ./templates/ /tmp/templates/
COPY ./rollup.config.js ./package.json ./package-lock.json ./tailwind.config.js /tmp/
COPY ./js/ /tmp/js/

RUN \
  mkdir -p /tmp/public/ \
  && npm i \
  && npm run build


# Main stage

FROM nginx:1.23.0-alpine

COPY . /var/www/
COPY ./nginx.conf /etc/nginx/nginx.conf
COPY --from=builder /tmp/public/styles.css /tmp/public/app.js /var/www/public/
