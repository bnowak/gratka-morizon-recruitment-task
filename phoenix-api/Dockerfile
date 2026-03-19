FROM elixir:1.15-alpine AS base

RUN apk add --no-cache build-base git nodejs npm inotify-tools bash

RUN addgroup -g 1000 app && adduser -u 1000 -G app -D app

WORKDIR /app
RUN chown app:app /app

USER app

RUN mix local.hex --force && mix local.rebar --force

FROM base AS dev

ENV MIX_ENV=dev

USER root
COPY ./entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER app

EXPOSE 4000

ENTRYPOINT ["/entrypoint.sh"]

FROM base AS prod

ENV MIX_ENV=prod

COPY --chown=app:app mix.exs mix.lock ./
COPY --chown=app:app config ./config

RUN mix deps.get --only prod && mix deps.compile

COPY --chown=app:app lib lib
COPY --chown=app:app priv priv

RUN mix compile && mix release

EXPOSE 4000

CMD ["_build/prod/rel/phoenix_api/bin/phoenix_api", "start"]
