namespace :pear do
  desc "Generate package.xml"
  task :xml, :version do |t, args|
    args.version(ENV['version'])
    unless args.version then
      puts 'Version number not given. Use "pear:xml[1.0]" or "pear:xml version=1.0"'
      exit 1
    end

    tplfile = 'package.pear'
    xmlfile = 'library/package.xml'

    # Remove old package.xml
    rm_f xmlfile

    # Get template contents
    text = File.read(tplfile)
    # Replace the version
    text = text.gsub("{VERSION}", args.version)

    dirs = []
    Dir.glob('library/**/*.*') do |file|
      file[0, 'library/'.length] = ''
      dirs << '<file name="' + file + '" role="php">'
      dirs << '<tasks:replace from="@package_version@" to="version" type="package-info" />'
      dirs << '</file>'

    end

    text = text.gsub('{DIRS}', dirs.join("\n"))

    # Generate a new pear package.xml
    xml = File.new(xmlfile, 'w')
    xml.syswrite(text);
    xml.close();
  end

  desc "Build a release"
  task :package => [:xml] do
    exec 'pear package -n "library/package.xml"'
  end


end