# Use Watchr to monitor for changes and run specs
#
# Example:
# watchr watchr.rb
#

watch('tests/.*\.spec\.php') do |md|
  system "./spec4php.php -b #{md}"
end

watch('library/DrSlump/.*') do |md|
  system "./spec4php.php -b tests/"
end

