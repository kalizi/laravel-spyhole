const sendRecordings = (recordings) => {
    if (
        !window.hasOwnProperty('spyholeConfig') ||
        !window.spyholeConfig.hasOwnProperty('storeUrl')
    )
        throw new Error('Missing spyhole configuration');

    recordings['path'] = window.location.pathname;

    fetch(window.spyholeConfig.storeUrl, {
        method: 'POST',
        body: JSON.stringify(recordings),
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.spyholeConfig.xsrf,
        },
    })
        .then((res) => res.json())
        .then((res) => {
            if (res.success) {
                window.spyholeDom.currentPage.recording = res.recording;
            }
        })
        .catch(() => {
            setTimeout(() => sendRecordings(recordings), 500);
        });
};

const initializeRecordings = () => {
    if (
        !(
            window.hasOwnProperty('spyholeConfig') &&
            window.spyholeConfig.hasOwnProperty('storeUrl') &&
            window.spyholeConfig.hasOwnProperty('samplingRate') &&
            window.spyholeConfig.hasOwnProperty('xsrf') &&
            window.hasOwnProperty('spyholeDom') &&
            window.spyholeDom.hasOwnProperty('domSent')
        )
    )
        throw new Error('Missing spyhole configuration');

    rrweb.record({
        emit(event) {
            if (!window.hasOwnProperty('spyholeEvents'))
                window.spyholeEvents = [];
            // push event into the events array
            window.spyholeEvents.push(event);

            if (
                window.spyholeEvents.length >= window.spyholeConfig.samplingRate
            ) {
                let payload = {
                    frames: window.spyholeEvents,
                    path: window.location.pathname,
                };
                window.spyholeEvents = [];
                if (!window.spyholeDom.domSent) {
                    payload['scene'] = document.documentElement.innerHTML;
                    window.spyholeDom.domSent = true;
                }
                if (window.spyholeDom.currentPage.recording !== null) {
                    payload['recording'] =
                        window.spyholeDom.currentPage.recording;
                }
                sendRecordings(payload);
            }
        },
    });

    window.addEventListener('beforeunload', () => {
        // Send remaining recordings
        sendRecordings({
            frames: window.spyholeEvents,
        });
    });
};

(() => {
    initializeRecordings();
})();
