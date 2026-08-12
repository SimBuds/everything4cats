# A throwaway stand-in for a freshly created Ubuntu 24.04 host.
#
# Deliberately bare: only what a provider image already has (ca-certificates,
# curl, sudo, python3). Everything else must come from provision.sh, or the
# test is not testing provision.sh.
FROM ubuntu:24.04

RUN apt-get update -qq \
 && apt-get install -y -qq --no-install-recommends ca-certificates curl sudo python3 \
 && rm -rf /var/lib/apt/lists/*

COPY repo /repo
COPY drive.py /drive.py
COPY verify.sh /verify.sh

# The theme landed 2026-08-12, so the harness now provisions with it active
# instead of leaving WordPress on twentytwentyfive. Set as ENV rather than
# inline so drive.py and verify.sh both see it: drive.py passes os.environ
# through, and verify.sh runs in its own layer where an inline variable would
# not survive. Until this existed the theme's templates had never been executed
# by anything.
ENV THEME_DIR=theme

# Run 1: the first-ever provision of an empty host.
RUN python3 /drive.py

# Run 2: the same script again, unchanged, on the host it just built.
# This is the redeploy case. It must not prompt, must not duplicate, must not
# fail, and must leave the host in the same state.
RUN python3 /drive.py

RUN bash /verify.sh
