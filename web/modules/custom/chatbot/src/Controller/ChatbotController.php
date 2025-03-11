<?php

namespace Drupal\chatbot\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides chatbot API responses.
 */
class ChatbotController {

  /**
   * Get the most recently joined student.
   */
  public function getRecentStudent() {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'student')
      ->sort('created', 'DESC')
      ->range(0, 1)
      ->execute();

    if ($query) {
      $nid = reset($query);
      $node = Node::load($nid);
      return new JsonResponse(['name' => $node->getTitle()]);
    }
    return new JsonResponse(['error' => 'No students found.'], 404);
  }

  /**
   * Get 5 students from a specific department.
   */
  public function getStudentsByDepartment(Request $request, $dept) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'student')
      ->condition('field_department', $dept)
      ->range(0, 5)
      ->execute();

    if ($query) {
      $students = [];
      foreach ($query as $nid) {
        $node = Node::load($nid);
        $students[] = $node->getTitle();
      }
      return new JsonResponse(['students' => $students]);
    }
    return new JsonResponse(['error' => 'No students found for this department.'], 404);
  }

  /**
   * Get teachers from a specific department.
   */
  public function getTeachersByDepartment(Request $request, $dept) {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'teacher')
      ->condition('field_department', $dept)
      ->execute();

    if ($query) {
      $teachers = [];
      foreach ($query as $nid) {
        $node = Node::load($nid);
        $teachers[] = $node->getTitle();
      }
      return new JsonResponse(['teachers' => $teachers]);
    }
    return new JsonResponse(['error' => 'No teachers found for this department.'], 404);
  }

}
