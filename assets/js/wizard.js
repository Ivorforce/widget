var Widget = Widget || {};
Widget.Wizard = function ()
{
  var $wizard = $('.wizard');
  var $form = $wizard.find('form');
  var $input = $form.find('input[name="url"]');
  var $creator = $wizard.find('.creator');

  var init = function() {
    showWizard();
    $form.on('submit', formSubmission);
  };

  var showWizard = function() {
    $form.show();
    $wizard.show();
  };

  var formSubmission = function() {
    event.preventDefault();
    var inputUrl = $input.val();
    $input.attr('disabled', 'disabled');
    var projectIdentifier = parseProjectUrl(inputUrl);
    fetchProjectData(projectIdentifier);
  };

  var parseProjectUrl = function(url) {
    var projectRegex = /^(?:.*\.com)?(?:\/)?(.*)$/;
    return projectRegex.exec(url)[1];
  }

  var fetchProjectData = function(projectIdentifier) {
    var data = $.ajax({
      url: '/' + projectIdentifier + '.json',
      type: 'GET',
      cache: false,
      dataType: 'json'
    });
    data.done(launchWizard);
    // TODO: Error handling
  }

  var launchWizard = function(projectData) {
    $form.hide();
    renderTemplate('project', projectData, $creator.find('.project'));
    renderTemplate('versions', prepareVersions(projectData['versions']), $creator.find('.versions'));
    $creator.show();
    // renderTemplate('generator', projectData, $('.generator'));
    // populate the menu
    // hide the form
    // $form.hide();
    // show the wizard
    // bind the events
  }

  var prepareVersions = function(versions) {
    var versionList = [];
    $.each(versions, function(version, files) {
      versionList.push({version : version, files: files.length});
    });
    return {versions: versionList};
  }

  var renderTemplate = function(template, data, target) {
    var template = $('#' + template + '-template').html();
    Mustache.parse(template);
    var rendered = Mustache.render(template, data);
    target.html(rendered);
  }

  var oPublic = {
    init: init
  }

  return oPublic;
}();