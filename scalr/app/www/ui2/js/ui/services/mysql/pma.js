Scalr.regPage('Scalr.ui.services.mysql.pma', function (loadParams, moduleParams) {
	if (moduleParams.redirectError)
		Scalr.message.Error(moduleParams.redirectError);

	Scalr.event.fireEvent('redirect', moduleParams.redirect);
});
