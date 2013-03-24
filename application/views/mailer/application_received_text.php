Thanks for applying for <?= __('r.app_name') ?>! We have received your application, and it is now being reviewed.

In terms of what to expect -- because of the volume of applications we're receiving, it will likely be a few weeks before you hear anything back from us. If your application seems promising, individual agencies will reach out to you to conduct interviews. If not, you will receive an email letting you know you have not been selected, or recommending that you apply to another project.

Your application means a lot. Thank you for being willing to serve and to use your skills to better your government and your country.

For your records, here's your application:

<?= View::make('vendors.partials.application_text')->with('vendor', $vendor) ?>


<?= __('r.email_signature_text') ?>
