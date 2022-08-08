# Build stage

FROM node:18-bullseye AS builder

WORKDIR /tmp/

COPY ./templates/ /tmp/templates/
COPY ./package.json ./package-lock.json /tmp/

RUN \
  mkdir -p /tmp/public/ \
  && npm i \
  && npm run build


# Main stage

FROM nginx:1.23.0-alpine

COPY . /var/www/
COPY ./nginx.conf /etc/nginx/nginx.conf
COPY --from=builder /tmp/public/styles.css /var/www/public/styles.css
