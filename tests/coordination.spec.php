<?php

describe "Coordination" {

    it. "should coordinate with OR" {
        1 should be equal 0 or be an integer;
        1 should be less than 0 or equal 1;
        1 should be a string or less than 0 or equal 1;
        1 should be a string, less than 0 or equal 1;
    }

    it. "should coordinate with AND" {
        1 should be an integer and equal 1;
        1 should equal 1 and be less than 2;

        1 should be more than 0 and less than 2;
        1 should be an integer and less than 2 and equal 1;
    }

    it. "should coordinate with BUT" 
        1 should be an integer but equal 1;
        1 should be an integer, but equal 1;
        1 should be an integer, and equal 1;
    end

    it. "should bind AND stronger than OR"
        "1" should be a boolean or a string and equal "1" or null;
        "1" should be a string and equal "1" or an integer;
    end

    it. "should bind OR stronger than BUT"
        1 should be an integer or a string but equal 1;
        1 should be an integer or a string, and equal 1;
    end

    it. "should support AND, OR and BUT together"
        "1" should be a bool or a string and equal "1" but more than 0;
        "1" should be a bool or a string and equal "1", and more than 0;
    end

    it. "should use last matcher if none given"
        1 should equal 0 or 1;
        1 should equal -1, 0 or 1;
    end

}
