<?php Section::inject('page_title', 'Application Received') ?>
<?php Section::inject('no_page_header', true) ?>
<div class="alert alert-success">
  Your application has been received! You will receive an email confirmation shortly.
  Thanks for applying and for being willing to dedicate your skills and abilities to serve your country.
</div>
<h4>Voluntary Demographic Survey</h4>
<p class="main-description">
  The White House is committed to equal opportunity, diversity, and inclusion in the Presidential Innovation Fellows program.
  Help us measure our progress by taking the following voluntary survey.
</p>
<div class="row-fluid">
  <div class="span6">
    <form action="<?php echo e(route('vendor_applied', $vendor->demographic_survey_key)); ?>" method="POST">
      <fieldset>
        <h5>Gender</h5>
        <select name="gender">
          <option value=""></option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
          <option value="Other">Other</option>
        </select>
        <h5>Race and Ethnicity (select up to two)</h5>
        <div class="demographic-checkboxes" data-checkbox-max="2">
          <label class="checkbox">
            <input type="checkbox" name="race[]" value="hispanic_latino" />
            Hispanic or Latino
            <?php echo Jade\Dumper::_html(Helper::helper_tooltip("A person of Cuban, Mexican, Puerto Rican, South or Central American, or other Spanish culture or origin regardless of race.", "right", false, true)) ?>
          </label>
          <label class="checkbox">
            <input type="checkbox" name="race[]" value="white" />
            White
            <?php echo Jade\Dumper::_html(Helper::helper_tooltip("A person having origins in any of the original peoples of Europe, the Middle East, or North Africa.", "right", false, true)) ?>
          </label>
          <label class="checkbox">
            <input type="checkbox" name="race[]" value="black" />
            Black or African American
            <?php echo Jade\Dumper::_html(Helper::helper_tooltip("A person having origins in any of the black racial groups of Africa.", "right", false, true)) ?>
          </label>
          <label class="checkbox">
            <input type="checkbox" name="race[]" value="pacific_islander" />
            Native Hawaiian or Other Pacific Islander
            <?php echo Jade\Dumper::_html(Helper::helper_tooltip("A person having origins in any of the peoples of Hawaii, Guam, Samoa, or other Pacific Islands.", "right", false, true)) ?>
          </label>
          <label class="checkbox">
            <input type="checkbox" name="race[]" value="asian" />
            Asian
            <?php echo Jade\Dumper::_html(Helper::helper_tooltip("A person having origins in any of the original peoples of the Far East, Southeast Asia, or the Indian Subcontinent, including, for example, Cambodia, China, India, Japan, Korea, Malaysia, Pakistan, the Philippine Islands, Thailand, and Vietnam.", "right", false, true)) ?>
          </label>
          <label class="checkbox">
            <input type="checkbox" name="race[]" value="american_indian" />
            American Indian or Alaska Native
            <?php echo Jade\Dumper::_html(Helper::helper_tooltip("A person having origins in any of the original peoples of North and South America (including Central America), and who maintain tribal affiliation or community attachment.", "right", false, true)) ?>
          </label>
        </div>
      </fieldset>
      <div class="form-actions">
        <button class="btn btn-primary">Submit</button>
      </div>
    </form>
  </div>
  <div class="span6">
    <div class="well">
      <p>
        <strong>WHITE HOUSE PRESIDENTIAL INNOVATION FELLOWS EEO SURVEY – APPLICANT VOLUNTARY SELF-IDENTIFICATION FORM</strong>
      </p>
      <p>
        Providing this information is voluntary and providing or refusing to provide it will not subject you to any adverse treatment.
        The information you provide will only be used for equal employment and diversity recordkeeping and reporting required by law.
        The information you provide is also confidential.
      </p>
      <p>
        To comply with federal equal employment opportunity recordkeeping and reporting requirements, the White House
        offers applicants the opportunity to complete this self-identification form to obtain certain demographic information.
        The information will be used only in accordance with the provisions of applicable laws, regulations and executive orders,
        including those that require information to be summarized and reported to the government.
      </p>
    </div>
  </div>
</div>