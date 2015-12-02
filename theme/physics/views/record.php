<?php

$author_field = $this->skylight_utilities->getField("Author");
$type_field = $this->skylight_utilities->getField("Type");
$bitstream_field = $this->skylight_utilities->getField("Bitstream");
$thumbnail_field = $this->skylight_utilities->getField("Thumbnail");
$filters = array_keys($this->config->item("skylight_filters"));

$type = 'Unknown';

if(isset($solr[$type_field])) {
    $type = "media-" . strtolower(str_replace(' ','-',$solr[$type_field][0]));
}


?>


<h1 class="itemtitle"><?php echo $record_title ?></h1>
<div class="tags">
    <?php

    if (isset($solr[$author_field])) {
        foreach($solr[$author_field] as $author) {
            $orig_filter = preg_replace('/ /','+',$author, -1);
            $orig_filter = preg_replace('/,/','%2C',$orig_filter, -1);
            echo '<a href=\'./search/*/Author:"'.$orig_filter.'"\'>'.$author.'</a>';
        }
    }

    ?>
</div>

<div class="content">

    <?php
    $abstract_field = $this->skylight_utilities->getField("Abstract");
    if(isset($solr[$abstract_field])) {
        ?> <h3>Abstract</h3> <?php
        foreach($solr[$abstract_field] as $abstract) {
            echo '<p>'.$abstract.'</p>';
        }
    }
    ?>

    <table>
        <caption>Description</caption>
        <tbody>
        <?php foreach($recorddisplay as $key) {

            $element = $this->skylight_utilities->getField($key);
            if(isset($solr[$element])) {
                echo '<tr><th>'.$key.'</th><td>';
                foreach($solr[$element] as $index => $metadatavalue) {
                    // if it's a facet search
                    // make it a clickable search link
                    if(in_array($key, $filters)) {

                        $orig_filter = urlencode($metadatavalue);
                        $lower_orig_filter = strtolower($metadatavalue);
                        $lower_orig_filter = urlencode($lower_orig_filter);

                        echo '<a href="./search/*:*/' . $key . ':%22'.$lower_orig_filter.'%7C%7C%7C'.$orig_filter.'%22">'.$metadatavalue.'</a>';
                    }
                    else {
                        echo $metadatavalue;
                    }
                    if($index < sizeof($solr[$element]) - 1) {
                        echo '; ';
                    }
                }
                echo '</td></tr>';
            }

        } ?>
        </tbody>
    </table>

</div>

<?php if(isset($solr[$bitstream_field]) && $link_bitstream) { ?>
    <div class="record_bitstreams">
        <h3>Digital Objects</h3>
        <?php //if ($isAuthorised != '1') { ?>
            <p>For performance and security reasons, where the source file is large, a thumbnail only will show.
                To see the high-resolution image, please contact <a href="./feedback">email</a>.</p>
        <?php// } else { ?>
            <!--<p>Click on a thumbnail to see the high resolution image.</p>-->
        <?php// } ?>

    <?php
    foreach($solr[$bitstream_field] as $bitstream) {

        $bitstreamLink = $this->skylight_utilities->getBitstreamLink($bitstream);
        $bitstreamLinkedImage = $this->skylight_utilities->getBitstreamLinkedImage($bitstream);

        $segments = explode("##", $bitstream);
        $filename = $segments[1];
        $filesize = $segments[2];
        //echo 'FILESIZE'.$filesize;
        $handle = $segments[3];
        $seq = $segments[4];
        $handle_id = preg_replace('/^.*\//', '',$handle);
        $uri = './record/'.$handle_id.'/'.$seq.'/'.$filename;

        if ($filesize > 1500000 or strpos($filename, ".tif") > 0)
        {
            $isAuthorised = '0';
        }
        else
        {
            $isAuthorised = '1';
        }

       // echo $isAuthorised;
        //echo $solr[$thumbnail_field];
        //echo $uri;

        if(isset($solr[$thumbnail_field])) {

            foreach ($solr[$thumbnail_field] as $thumbnail) {
                $t_segments = explode("##", $thumbnail);

                $t_filename = $t_segments[1];
                $t_handle = $t_segments[3];
                $t_seq = $t_segments[4];
                $handle_id = preg_replace('/^.*\//', '',$t_handle);
                $t_uri = './record/'.$handle_id.'/'.$t_seq.'/'.$t_filename;
               // echo $t_uri;

                if ($t_filename == $filename.'.jpg') {
                    if ($isAuthorised != '1') {
                        //echo 'using thumbnal';
                        $thumbnailLink = '<img src = "'.$t_uri.'" title="'. $solr[$title_field][0] .'" />';
                    } else {
                       // echo 'using bitstream';
                        $thumbnailLink = '<a title = "' . $solr[$title_field][0] . '" class="fancybox"' . ' href="' . $uri . '"><img src = "'.$t_uri.'" title="'. $solr[$title_field][0] .'" /></a> ';
                    }
                    echo $thumbnailLink;
                }
            }
        }

    }


    ?>
    </div>
<?php
} ?>

<input type="button" value="Back to Search Results" class="backbtn" onClick="history.go(-1);">
