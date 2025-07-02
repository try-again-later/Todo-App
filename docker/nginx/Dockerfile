# Build stage

FROM node:22-alpine AS builder

WORKDIR /var/www
COPY . /var/www
RUN \
  npm install && \
  npm run build


# Main stage

FROM nginx:1.27.5-alpine

COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY --from=builder /var/www/public /var/www/public

WORKDIR /var/www/public
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
