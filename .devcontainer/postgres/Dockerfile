FROM postgres:bullseye

COPY entry.sh /usr/local/bin/entry.sh
RUN chmod +x /usr/local/bin/entry.sh

ENTRYPOINT [ "entry.sh", "docker-entrypoint.sh" ]

CMD [ "postgres" ]
