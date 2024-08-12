FROM alpine:3.20.2

ARG NFDUMP_VERSION
ARG UID
ARG GID

ENV NFDUMP_VERSION=${NFDUMP_VERSION}
ENV UID=${UID}
ENV GID=${GID}

RUN mkdir -p /netflowData

# MacOS staff group's gid is 20
RUN delgroup dialout

RUN addgroup -g ${GID} --system laravel
RUN adduser -G laravel --system -D -s /bin/sh -u ${UID} laravel

WORKDIR /tmp

## Install OS dependencies
ADD https://github.com/phaag/nfdump/archive/v${NFDUMP_VERSION}.tar.gz /tmp
RUN apk add --no-cache libtool bzip2-dev curl
RUN apk add --no-cache --virtual build-deps autoconf automake m4 pkgconfig make g++ flex byacc

#Build
RUN  \
    tar xfz v${NFDUMP_VERSION}.tar.gz  \
    && cd /tmp/nfdump-${NFDUMP_VERSION} \
    && ./autogen.sh  \
    && ./configure  \
    && make  \
    && cd /tmp/nfdump-${NFDUMP_VERSION} && make install  \
    && cd .. \
    && rm -rf nfdump-${NFDUMP_VERSION}  \
    && rm /tmp/v${NFDUMP_VERSION}.tar.gz  \
    && apk del build-deps

ADD ./process_data.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/process_data.sh

RUN chown -R laravel:laravel /netflowData
USER laravel

CMD ["nfcapd", "-w", "/netflowData", "-S", "1", "-z", "-p", "2055", "-t", "300", "-x", "/usr/local/bin/process_data.sh %d/%f"]
