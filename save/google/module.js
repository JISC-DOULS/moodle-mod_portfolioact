M.portfolioactsave_google = M.portfolioactsave_google || {};

M.portfolioactsave_google.googleexport = function(Y, msg) {
    Y.one('.portfolioactsave_google_spinner').setStyle('display', 'none');

};

M.portfolioactsave_google.init = function(Y, actid, mode, cmid, savetype) {

    M.portfolioactsave_google.actid = actid;
    M.portfolioactsave_google.mode = mode;
    // data = {'actid' : actid, 'mode' : mode, 'cmid' : cmid, 'savetype' :
    // savetype};

    processexport = function() {

        Y.one('.portfolioactsave_google_patient_message').setStyle('display',
                'block');
        Y.one('.portfolioactsave_google_spinner').setStyle('display', 'block');

        googleresult = function() {

        };

        url = M.cfg.wwwroot + '/mod/portfolioact/save/google/export.php';

        var cfg = {
            method : 'POST',
            data : 'actid=' + actid + '&mode=' + mode + '&cmid=' + cmid
                    + '&savetype=' + savetype,
            timeout : 1000 * 60 * 5, // google takes a long time

            on : {
                success : function(ioId, o) {

                    result = Y.JSON.parse(o.responseText);

                    if (result.status == 1) {
                        Y.one('.portfolioactsave_google_spinner').setStyle(
                                'display', 'none');
                        Y.one('.portfolioactsave_google_message_success')
                                .setStyle('display', 'block');
                        if (result.optional_message !== undefined) {
                            Y.one('.portfolioactsave_google_message_success')
                                    .append(result.optional_message);
                        }

                    } else {
                        Y.one('.portfolioactsave_google_spinner').setStyle(
                                'display', 'none');
                        Y.one('.portfolioactsave_google_message_error').append(
                                result.error_message);
                        Y.one('.portfolioactsave_google_message_error')
                                .setStyle('display', 'block');
                    }
                },
                failure : function(ioId, o) {
                    Y.one('.portfolioactsave_google_message_error').setStyle(
                            'display', 'block');

                }
            }
        };
        Y.io(url, cfg);

    };

    processexport();

};
